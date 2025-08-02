using HeyDav.Domain.Workflows.Entities;
using HeyDav.Domain.Workflows.Enums;
using HeyDav.Domain.Workflows.ValueObjects;
using HeyDav.Application.Workflows.Interfaces;
using HeyDav.Application.Workflows.Models;

namespace HeyDav.Application.Workflows.Engines;

public class WorkflowTemplateEngine : IWorkflowTemplateEngine
{
    private readonly IWorkflowTemplateRepository _workflowTemplateRepository;
    private readonly IWorkflowInstanceRepository _workflowInstanceRepository;
    private readonly IWorkflowAnalytics _workflowAnalytics;

    public WorkflowTemplateEngine(
        IWorkflowTemplateRepository workflowTemplateRepository,
        IWorkflowInstanceRepository workflowInstanceRepository,
        IWorkflowAnalytics workflowAnalytics)
    {
        _workflowTemplateRepository = workflowTemplateRepository;
        _workflowInstanceRepository = workflowInstanceRepository;
        _workflowAnalytics = workflowAnalytics;
    }

    public async Task<WorkflowTemplate> CreateTemplateAsync(CreateWorkflowTemplateRequest request, CancellationToken cancellationToken = default)
    {
        var template = WorkflowTemplate.Create(
            request.Name,
            request.Description,
            request.Category,
            request.Difficulty,
            request.EstimatedDuration,
            request.IsBuiltIn,
            request.CreatedBy);

        if (request.AutoTrigger != null)
        {
            template.SetAutoTrigger(request.AutoTrigger);
        }

        if (!string.IsNullOrEmpty(request.ConfigurationSchema))
        {
            template.SetConfigurationSchema(request.ConfigurationSchema);
        }

        // Add step templates
        foreach (var stepRequest in request.StepTemplates.OrderBy(s => s.Order))
        {
            template.AddStepTemplate(
                stepRequest.Name,
                stepRequest.Description,
                stepRequest.Type,
                stepRequest.Order,
                stepRequest.IsRequired,
                stepRequest.Configuration);
        }

        // Add tags
        foreach (var tag in request.Tags)
        {
            template.AddTag(tag);
        }

        await _workflowTemplateRepository.AddAsync(template, cancellationToken);
        await _workflowTemplateRepository.SaveChangesAsync(cancellationToken);

        return template;
    }

    public async Task<WorkflowTemplate> UpdateTemplateAsync(Guid templateId, UpdateWorkflowTemplateRequest request, CancellationToken cancellationToken = default)
    {
        var template = await _workflowTemplateRepository.GetByIdAsync(templateId, cancellationToken);
        if (template == null)
            throw new ArgumentException($"Workflow template with ID {templateId} not found");

        template.UpdateDetails(request.Name, request.Description, request.Difficulty, request.EstimatedDuration);

        if (request.AutoTrigger != null)
        {
            template.SetAutoTrigger(request.AutoTrigger);
        }

        if (!string.IsNullOrEmpty(request.ConfigurationSchema))
        {
            template.SetConfigurationSchema(request.ConfigurationSchema);
        }

        await _workflowTemplateRepository.SaveChangesAsync(cancellationToken);
        return template;
    }

    public async Task<WorkflowTemplate> CreateTemplateVersionAsync(Guid templateId, CancellationToken cancellationToken = default)
    {
        var originalTemplate = await _workflowTemplateRepository.GetByIdAsync(templateId, cancellationToken);
        if (originalTemplate == null)
            throw new ArgumentException($"Workflow template with ID {templateId} not found");

        var newVersion = originalTemplate.CreateNewVersion();
        await _workflowTemplateRepository.AddAsync(newVersion, cancellationToken);
        await _workflowTemplateRepository.SaveChangesAsync(cancellationToken);

        return newVersion;
    }

    public async Task<List<WorkflowTemplate>> GetTemplatesAsync(WorkflowTemplateFilter? filter = null, CancellationToken cancellationToken = default)
    {
        return await _workflowTemplateRepository.GetTemplatesAsync(filter, cancellationToken);
    }

    public async Task<List<WorkflowTemplate>> GetRecommendedTemplatesAsync(string? userId = null, int count = 10, CancellationToken cancellationToken = default)
    {
        var userPatterns = await _workflowAnalytics.GetUserPatternsAsync(userId, cancellationToken);
        var templates = await _workflowTemplateRepository.GetActiveTemplatesAsync(cancellationToken);

        return templates
            .Where(t => IsRecommendedForUser(t, userPatterns))
            .OrderByDescending(t => CalculateRecommendationScore(t, userPatterns))
            .Take(count)
            .ToList();
    }

    public async Task<WorkflowInstance> CreateInstanceFromTemplateAsync(Guid templateId, CreateWorkflowInstanceRequest request, CancellationToken cancellationToken = default)
    {
        var template = await _workflowTemplateRepository.GetByIdAsync(templateId, cancellationToken);
        if (template == null)
            throw new ArgumentException($"Workflow template with ID {templateId} not found");

        if (!template.IsActive)
            throw new InvalidOperationException("Cannot create instance from inactive template");

        var instance = WorkflowInstance.Create(
            templateId,
            request.Name ?? template.Name,
            request.UserId,
            request.TriggerSource,
            request.Configuration);

        instance.InitializeFromTemplate(template);

        // Apply any configuration overrides
        if (!string.IsNullOrEmpty(request.Configuration))
        {
            ApplyConfigurationOverrides(instance, request.Configuration);
        }

        await _workflowInstanceRepository.AddAsync(instance, cancellationToken);
        await _workflowInstanceRepository.SaveChangesAsync(cancellationToken);

        // Track usage
        template.IncrementUsage();
        await _workflowTemplateRepository.SaveChangesAsync(cancellationToken);

        return instance;
    }

    public async Task<WorkflowTemplateAnalytics> GetTemplateAnalyticsAsync(Guid templateId, DateTime? fromDate = null, DateTime? toDate = null, CancellationToken cancellationToken = default)
    {
        var template = await _workflowTemplateRepository.GetByIdAsync(templateId, cancellationToken);
        if (template == null)
            throw new ArgumentException($"Workflow template with ID {templateId} not found");

        var instances = await _workflowInstanceRepository.GetByTemplateIdAsync(templateId, fromDate, toDate, cancellationToken);

        return new WorkflowTemplateAnalytics
        {
            TemplateId = templateId,
            TemplateName = template.Name,
            TotalUsage = instances.Count,
            SuccessRate = instances.Count > 0 ? (decimal)instances.Count(i => i.Status == WorkflowStatus.Completed) / instances.Count * 100 : 0,
            AverageCompletionTime = CalculateAverageCompletionTime(instances),
            MostCommonFailurePoints = GetMostCommonFailurePoints(instances),
            UserSatisfactionScore = template.Rating,
            OptimizationSuggestions = await GenerateOptimizationSuggestionsAsync(template, instances, cancellationToken)
        };
    }

    public async Task<List<WorkflowOptimizationSuggestion>> GetOptimizationSuggestionsAsync(Guid templateId, CancellationToken cancellationToken = default)
    {
        var template = await _workflowTemplateRepository.GetByIdAsync(templateId, cancellationToken);
        if (template == null)
            throw new ArgumentException($"Workflow template with ID {templateId} not found");

        var instances = await _workflowInstanceRepository.GetByTemplateIdAsync(templateId, cancellationToken: cancellationToken);
        return await GenerateOptimizationSuggestionsAsync(template, instances, cancellationToken);
    }

    public async Task<bool> ValidateTemplateAsync(Guid templateId, CancellationToken cancellationToken = default)
    {
        var template = await _workflowTemplateRepository.GetByIdAsync(templateId, cancellationToken);
        if (template == null)
            return false;

        // Validate template structure
        if (template.StepTemplates.Count == 0)
            return false;

        // Validate step order
        var orderedSteps = template.StepTemplates.OrderBy(s => s.Order).ToList();
        for (int i = 0; i < orderedSteps.Count; i++)
        {
            if (orderedSteps[i].Order != i + 1)
                return false;
        }

        // Validate required steps
        if (!template.StepTemplates.Any(s => s.IsRequired))
            return false;

        // Validate configuration schema if present
        if (!string.IsNullOrEmpty(template.ConfigurationSchema))
        {
            if (!IsValidJsonSchema(template.ConfigurationSchema))
                return false;
        }

        return true;
    }

    public async Task ArchiveTemplateAsync(Guid templateId, CancellationToken cancellationToken = default)
    {
        var template = await _workflowTemplateRepository.GetByIdAsync(templateId, cancellationToken);
        if (template == null)
            throw new ArgumentException($"Workflow template with ID {templateId} not found");

        template.Deactivate();
        await _workflowTemplateRepository.SaveChangesAsync(cancellationToken);
    }

    public async Task DeleteTemplateAsync(Guid templateId, CancellationToken cancellationToken = default)
    {
        var template = await _workflowTemplateRepository.GetByIdAsync(templateId, cancellationToken);
        if (template == null)
            throw new ArgumentException($"Workflow template with ID {templateId} not found");

        if (template.IsBuiltIn)
            throw new InvalidOperationException("Cannot delete built-in templates");

        // Check if there are active instances
        var activeInstances = await _workflowInstanceRepository.GetActiveInstancesByTemplateIdAsync(templateId, cancellationToken);
        if (activeInstances.Any())
            throw new InvalidOperationException("Cannot delete template with active instances");

        await _workflowTemplateRepository.DeleteAsync(templateId, cancellationToken);
        await _workflowTemplateRepository.SaveChangesAsync(cancellationToken);
    }

    private bool IsRecommendedForUser(WorkflowTemplate template, UserProductivityPatterns userPatterns)
    {
        // Basic recommendation logic - can be enhanced with ML
        if (template.Rating < 3.0m) return false;
        if (!template.IsActive) return false;

        // Check if template category aligns with user patterns
        if (userPatterns.PreferredCategories.Contains(template.Category))
            return true;

        // Check difficulty vs user experience
        if (template.Difficulty <= userPatterns.ExperienceLevel)
            return true;

        return false;
    }

    private decimal CalculateRecommendationScore(WorkflowTemplate template, UserProductivityPatterns userPatterns)
    {
        decimal score = 0;

        // Base score from rating and usage
        score += template.Rating * 20;
        score += Math.Min(template.UsageCount / 100.0m, 10);

        // Category preference bonus
        if (userPatterns.PreferredCategories.Contains(template.Category))
            score += 30;

        // Difficulty alignment
        if (template.Difficulty == userPatterns.ExperienceLevel)
            score += 20;
        else if (Math.Abs((int)template.Difficulty - (int)userPatterns.ExperienceLevel) == 1)
            score += 10;

        // Duration preference
        if (template.EstimatedDuration <= userPatterns.PreferredSessionDuration)
            score += 15;

        return Math.Min(score, 100);
    }

    private void ApplyConfigurationOverrides(WorkflowInstance instance, string configuration)
    {
        // Parse configuration JSON and apply overrides to step instances
        // Implementation depends on the configuration schema format
        try
        {
            var config = System.Text.Json.JsonSerializer.Deserialize<Dictionary<string, object>>(configuration);
            foreach (var kvp in config)
            {
                instance.SetContextValue(kvp.Key, kvp.Value);
            }
        }
        catch (Exception)
        {
            // Log error but don't fail instance creation
        }
    }

    private TimeSpan? CalculateAverageCompletionTime(List<WorkflowInstance> instances)
    {
        var completedInstances = instances.Where(i => i.ActualDuration.HasValue).ToList();
        if (!completedInstances.Any())
            return null;

        var totalTicks = completedInstances.Sum(i => i.ActualDuration!.Value.Ticks);
        return new TimeSpan(totalTicks / completedInstances.Count);
    }

    private List<string> GetMostCommonFailurePoints(List<WorkflowInstance> instances)
    {
        var failurePoints = new Dictionary<string, int>();

        foreach (var instance in instances.Where(i => i.Status == WorkflowStatus.Failed))
        {
            foreach (var step in instance.StepInstances.Where(s => s.Status == WorkflowStepStatus.Failed))
            {
                var key = $"{step.Name} (Order: {step.Order})";
                failurePoints[key] = failurePoints.GetValueOrDefault(key, 0) + 1;
            }
        }

        return failurePoints
            .OrderByDescending(kvp => kvp.Value)
            .Take(5)
            .Select(kvp => kvp.Key)
            .ToList();
    }

    private async Task<List<WorkflowOptimizationSuggestion>> GenerateOptimizationSuggestionsAsync(
        WorkflowTemplate template,
        List<WorkflowInstance> instances,
        CancellationToken cancellationToken)
    {
        var suggestions = new List<WorkflowOptimizationSuggestion>();

        // Analyze completion rates
        if (instances.Any())
        {
            var completionRate = (decimal)instances.Count(i => i.Status == WorkflowStatus.Completed) / instances.Count * 100;
            if (completionRate < 70)
            {
                suggestions.Add(new WorkflowOptimizationSuggestion
                {
                    Type = OptimizationType.Structure,
                    Priority = OptimizationPriority.High,
                    Title = "Low Completion Rate",
                    Description = $"Only {completionRate:F1}% of instances complete successfully. Consider simplifying the workflow or making some steps optional.",
                    Impact = "High"
                });
            }
        }

        // Analyze step failure patterns
        var failurePoints = GetMostCommonFailurePoints(instances);
        if (failurePoints.Any())
        {
            suggestions.Add(new WorkflowOptimizationSuggestion
            {
                Type = OptimizationType.Steps,
                Priority = OptimizationPriority.Medium,
                Title = "Common Failure Points",
                Description = $"Steps frequently fail: {string.Join(", ", failurePoints.Take(3))}. Consider adding guidance or making these steps optional.",
                Impact = "Medium"
            });
        }

        // Analyze duration vs estimates
        var avgDuration = CalculateAverageCompletionTime(instances);
        if (avgDuration.HasValue && avgDuration.Value > template.EstimatedDuration.Add(TimeSpan.FromMinutes(30)))
        {
            suggestions.Add(new WorkflowOptimizationSuggestion
            {
                Type = OptimizationType.Timing,
                Priority = OptimizationPriority.Low,
                Title = "Duration Estimate Too Low",
                Description = $"Actual completion time ({avgDuration.Value:hh\\:mm}) significantly exceeds estimate ({template.EstimatedDuration:hh\\:mm}). Consider updating the estimate.",
                Impact = "Low"
            });
        }

        return suggestions;
    }

    private bool IsValidJsonSchema(string schema)
    {
        try
        {
            System.Text.Json.JsonDocument.Parse(schema);
            return true;
        }
        catch
        {
            return false;
        }
    }
}