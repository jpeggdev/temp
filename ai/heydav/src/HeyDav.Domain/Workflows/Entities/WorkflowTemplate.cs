using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Workflows.Enums;
using HeyDav.Domain.Workflows.ValueObjects;

namespace HeyDav.Domain.Workflows.Entities;

public class WorkflowTemplate : AggregateRoot
{
    private readonly List<WorkflowStepTemplate> _stepTemplates = new();
    private readonly List<string> _tags = new();

    public string Name { get; private set; }
    public string Description { get; private set; }
    public WorkflowCategory Category { get; private set; }
    public WorkflowDifficulty Difficulty { get; private set; }
    public TimeSpan EstimatedDuration { get; private set; }
    public bool IsActive { get; private set; }
    public bool IsBuiltIn { get; private set; }
    public string? CreatedBy { get; private set; }
    public int Version { get; private set; }
    public WorkflowTrigger? AutoTrigger { get; private set; }
    public string? ConfigurationSchema { get; private set; } // JSON schema for customization
    public int UsageCount { get; private set; }
    public decimal Rating { get; private set; }
    public int RatingCount { get; private set; }
    public IReadOnlyList<WorkflowStepTemplate> StepTemplates => _stepTemplates.AsReadOnly();
    public IReadOnlyList<string> Tags => _tags.AsReadOnly();

    private WorkflowTemplate(
        string name,
        string description,
        WorkflowCategory category,
        WorkflowDifficulty difficulty,
        TimeSpan estimatedDuration,
        bool isBuiltIn = false,
        string? createdBy = null)
    {
        Name = name;
        Description = description;
        Category = category;
        Difficulty = difficulty;
        EstimatedDuration = estimatedDuration;
        IsActive = true;
        IsBuiltIn = isBuiltIn;
        CreatedBy = createdBy;
        Version = 1;
        UsageCount = 0;
        Rating = 0;
        RatingCount = 0;
    }

    public static WorkflowTemplate Create(
        string name,
        string description,
        WorkflowCategory category,
        WorkflowDifficulty difficulty,
        TimeSpan estimatedDuration,
        bool isBuiltIn = false,
        string? createdBy = null)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("Workflow template name cannot be empty", nameof(name));

        if (string.IsNullOrWhiteSpace(description))
            throw new ArgumentException("Workflow template description cannot be empty", nameof(description));

        var template = new WorkflowTemplate(name, description, category, difficulty, estimatedDuration, isBuiltIn, createdBy);
        template.AddDomainEvent(new WorkflowTemplateCreatedEvent(template.Id, name));
        return template;
    }

    public void UpdateDetails(string name, string description, WorkflowDifficulty difficulty, TimeSpan estimatedDuration)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("Workflow template name cannot be empty", nameof(name));

        if (string.IsNullOrWhiteSpace(description))
            throw new ArgumentException("Workflow template description cannot be empty", nameof(description));

        Name = name;
        Description = description;
        Difficulty = difficulty;
        EstimatedDuration = estimatedDuration;
        UpdateTimestamp();
    }

    public void SetAutoTrigger(WorkflowTrigger trigger)
    {
        AutoTrigger = trigger;
        UpdateTimestamp();
    }

    public void SetConfigurationSchema(string schema)
    {
        ConfigurationSchema = schema;
        UpdateTimestamp();
    }

    public WorkflowStepTemplate AddStepTemplate(
        string name,
        string description,
        WorkflowStepType type,
        int order,
        bool isRequired = true,
        string? configuration = null)
    {
        var stepTemplate = WorkflowStepTemplate.Create(Id, name, description, type, order, isRequired, configuration);
        _stepTemplates.Add(stepTemplate);
        UpdateTimestamp();
        return stepTemplate;
    }

    public void RemoveStepTemplate(Guid stepTemplateId)
    {
        var stepTemplate = _stepTemplates.FirstOrDefault(s => s.Id == stepTemplateId);
        if (stepTemplate != null)
        {
            _stepTemplates.Remove(stepTemplate);
            UpdateTimestamp();
        }
    }

    public void ReorderStepTemplates(List<Guid> stepTemplateIds)
    {
        for (int i = 0; i < stepTemplateIds.Count; i++)
        {
            var stepTemplate = _stepTemplates.FirstOrDefault(s => s.Id == stepTemplateIds[i]);
            stepTemplate?.UpdateOrder(i + 1);
        }
        UpdateTimestamp();
    }

    public void AddTag(string tag)
    {
        if (!string.IsNullOrWhiteSpace(tag) && !_tags.Contains(tag))
        {
            _tags.Add(tag);
            UpdateTimestamp();
        }
    }

    public void RemoveTag(string tag)
    {
        if (_tags.Remove(tag))
        {
            UpdateTimestamp();
        }
    }

    public void Activate()
    {
        IsActive = true;
        UpdateTimestamp();
    }

    public void Deactivate()
    {
        IsActive = false;
        UpdateTimestamp();
    }

    public void IncrementUsage()
    {
        UsageCount++;
        UpdateTimestamp();
    }

    public void AddRating(int rating)
    {
        if (rating < 1 || rating > 5)
            throw new ArgumentOutOfRangeException(nameof(rating), "Rating must be between 1 and 5");

        var totalRating = (Rating * RatingCount) + rating;
        RatingCount++;
        Rating = totalRating / RatingCount;
        UpdateTimestamp();
    }

    public WorkflowTemplate CreateNewVersion()
    {
        var newTemplate = new WorkflowTemplate(Name, Description, Category, Difficulty, EstimatedDuration, IsBuiltIn, CreatedBy)
        {
            Version = Version + 1,
            AutoTrigger = AutoTrigger,
            ConfigurationSchema = ConfigurationSchema
        };

        foreach (var stepTemplate in _stepTemplates)
        {
            newTemplate.AddStepTemplate(stepTemplate.Name, stepTemplate.Description, stepTemplate.Type, stepTemplate.Order, stepTemplate.IsRequired, stepTemplate.Configuration);
        }

        foreach (var tag in _tags)
        {
            newTemplate.AddTag(tag);
        }

        return newTemplate;
    }
}

public class WorkflowStepTemplate : BaseEntity
{
    private readonly List<string> _dependencies = new();

    public Guid WorkflowTemplateId { get; private set; }
    public string Name { get; private set; }
    public string Description { get; private set; }
    public WorkflowStepType Type { get; private set; }
    public int Order { get; private set; }
    public bool IsRequired { get; private set; }
    public string? Configuration { get; private set; } // JSON configuration for step
    public TimeSpan? EstimatedDuration { get; private set; }
    public IReadOnlyList<string> Dependencies => _dependencies.AsReadOnly();

    private WorkflowStepTemplate(
        Guid workflowTemplateId,
        string name,
        string description,
        WorkflowStepType type,
        int order,
        bool isRequired,
        string? configuration)
    {
        WorkflowTemplateId = workflowTemplateId;
        Name = name;
        Description = description;
        Type = type;
        Order = order;
        IsRequired = isRequired;
        Configuration = configuration;
    }

    public static WorkflowStepTemplate Create(
        Guid workflowTemplateId,
        string name,
        string description,
        WorkflowStepType type,
        int order,
        bool isRequired = true,
        string? configuration = null)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("Step template name cannot be empty", nameof(name));

        return new WorkflowStepTemplate(workflowTemplateId, name, description, type, order, isRequired, configuration);
    }

    public void UpdateDetails(string name, string description, bool isRequired, string? configuration)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("Step template name cannot be empty", nameof(name));

        Name = name;
        Description = description;
        IsRequired = isRequired;
        Configuration = configuration;
        UpdateTimestamp();
    }

    public void UpdateOrder(int order)
    {
        Order = order;
        UpdateTimestamp();
    }

    public void SetEstimatedDuration(TimeSpan duration)
    {
        EstimatedDuration = duration;
        UpdateTimestamp();
    }

    public void AddDependency(string dependency)
    {
        if (!string.IsNullOrWhiteSpace(dependency) && !_dependencies.Contains(dependency))
        {
            _dependencies.Add(dependency);
            UpdateTimestamp();
        }
    }

    public void RemoveDependency(string dependency)
    {
        if (_dependencies.Remove(dependency))
        {
            UpdateTimestamp();
        }
    }
}

// Domain Events
public record WorkflowTemplateCreatedEvent(Guid WorkflowTemplateId, string Name) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record WorkflowTemplateUpdatedEvent(Guid WorkflowTemplateId) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}