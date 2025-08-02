using HeyDav.Domain.Workflows.Entities;
using HeyDav.Application.Workflows.Models;

namespace HeyDav.Application.Workflows.Interfaces;

public interface IWorkflowTemplateEngine
{
    Task<WorkflowTemplate> CreateTemplateAsync(CreateWorkflowTemplateRequest request, CancellationToken cancellationToken = default);
    Task<WorkflowTemplate> UpdateTemplateAsync(Guid templateId, UpdateWorkflowTemplateRequest request, CancellationToken cancellationToken = default);
    Task<WorkflowTemplate> CreateTemplateVersionAsync(Guid templateId, CancellationToken cancellationToken = default);
    Task<List<WorkflowTemplate>> GetTemplatesAsync(WorkflowTemplateFilter? filter = null, CancellationToken cancellationToken = default);
    Task<List<WorkflowTemplate>> GetRecommendedTemplatesAsync(string? userId = null, int count = 10, CancellationToken cancellationToken = default);
    Task<WorkflowInstance> CreateInstanceFromTemplateAsync(Guid templateId, CreateWorkflowInstanceRequest request, CancellationToken cancellationToken = default);
    Task<WorkflowTemplateAnalytics> GetTemplateAnalyticsAsync(Guid templateId, DateTime? fromDate = null, DateTime? toDate = null, CancellationToken cancellationToken = default);
    Task<List<WorkflowOptimizationSuggestion>> GetOptimizationSuggestionsAsync(Guid templateId, CancellationToken cancellationToken = default);
    Task<bool> ValidateTemplateAsync(Guid templateId, CancellationToken cancellationToken = default);
    Task ArchiveTemplateAsync(Guid templateId, CancellationToken cancellationToken = default);
    Task DeleteTemplateAsync(Guid templateId, CancellationToken cancellationToken = default);
}

public interface IWorkflowTemplateRepository
{
    Task<WorkflowTemplate?> GetByIdAsync(Guid id, CancellationToken cancellationToken = default);
    Task<List<WorkflowTemplate>> GetTemplatesAsync(WorkflowTemplateFilter? filter = null, CancellationToken cancellationToken = default);
    Task<List<WorkflowTemplate>> GetActiveTemplatesAsync(CancellationToken cancellationToken = default);
    Task AddAsync(WorkflowTemplate template, CancellationToken cancellationToken = default);
    Task DeleteAsync(Guid id, CancellationToken cancellationToken = default);
    Task<int> SaveChangesAsync(CancellationToken cancellationToken = default);
}

public interface IWorkflowInstanceRepository
{
    Task<WorkflowInstance?> GetByIdAsync(Guid id, CancellationToken cancellationToken = default);
    Task<List<WorkflowInstance>> GetByTemplateIdAsync(Guid templateId, DateTime? fromDate = null, DateTime? toDate = null, CancellationToken cancellationToken = default);
    Task<List<WorkflowInstance>> GetActiveInstancesByTemplateIdAsync(Guid templateId, CancellationToken cancellationToken = default);
    Task<List<WorkflowInstance>> GetByUserIdAsync(string userId, CancellationToken cancellationToken = default);
    Task AddAsync(WorkflowInstance instance, CancellationToken cancellationToken = default);
    Task<int> SaveChangesAsync(CancellationToken cancellationToken = default);
}

public interface IWorkflowAnalytics
{
    Task<UserProductivityPatterns> GetUserPatternsAsync(string? userId, CancellationToken cancellationToken = default);
    Task<List<WorkflowInsight>> GetWorkflowInsightsAsync(Guid? templateId = null, string? userId = null, CancellationToken cancellationToken = default);
    Task<ProductivityScore> CalculateProductivityScoreAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
}