using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Workflows.Enums;
using HeyDav.Domain.Workflows.ValueObjects;

namespace HeyDav.Domain.Workflows.Entities;

public class WorkflowInstance : AggregateRoot
{
    private readonly List<WorkflowStepInstance> _stepInstances = new();
    private readonly Dictionary<string, object> _context = new();

    public Guid WorkflowTemplateId { get; private set; }
    public string Name { get; private set; }
    public WorkflowStatus Status { get; private set; }
    public DateTime? StartedAt { get; private set; }
    public DateTime? CompletedAt { get; private set; }
    public DateTime? PausedAt { get; private set; }
    public string? UserId { get; private set; }
    public WorkflowTrigger? TriggerSource { get; private set; }
    public string? Configuration { get; private set; } // JSON configuration overrides
    public decimal Progress { get; private set; } // 0-100
    public TimeSpan? ActualDuration { get; private set; }
    public WorkflowResult? Result { get; private set; }
    public string? Notes { get; private set; }
    public IReadOnlyList<WorkflowStepInstance> StepInstances => _stepInstances.AsReadOnly();
    public IReadOnlyDictionary<string, object> Context => _context.AsReadOnly();

    private WorkflowInstance(
        Guid workflowTemplateId,
        string name,
        string? userId = null,
        WorkflowTrigger? triggerSource = null,
        string? configuration = null)
    {
        WorkflowTemplateId = workflowTemplateId;
        Name = name;
        UserId = userId;
        TriggerSource = triggerSource;
        Configuration = configuration;
        Status = WorkflowStatus.NotStarted;
        Progress = 0;
    }

    public static WorkflowInstance Create(
        Guid workflowTemplateId,
        string name,
        string? userId = null,
        WorkflowTrigger? triggerSource = null,
        string? configuration = null)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("Workflow instance name cannot be empty", nameof(name));

        var instance = new WorkflowInstance(workflowTemplateId, name, userId, triggerSource, configuration);
        instance.AddDomainEvent(new WorkflowInstanceCreatedEvent(instance.Id, workflowTemplateId, name));
        return instance;
    }

    public void InitializeFromTemplate(WorkflowTemplate template)
    {
        foreach (var stepTemplate in template.StepTemplates.OrderBy(s => s.Order))
        {
            var stepInstance = WorkflowStepInstance.CreateFromTemplate(Id, stepTemplate);
            _stepInstances.Add(stepInstance);
        }
        UpdateTimestamp();
    }

    public void Start()
    {
        if (Status != WorkflowStatus.NotStarted && Status != WorkflowStatus.Paused)
            throw new InvalidOperationException($"Cannot start workflow in {Status} status");

        Status = WorkflowStatus.Running;
        StartedAt = DateTime.UtcNow;
        UpdateTimestamp();
        AddDomainEvent(new WorkflowInstanceStartedEvent(Id, StartedAt.Value));

        // Start the first step if available
        var firstStep = _stepInstances.OrderBy(s => s.Order).FirstOrDefault();
        if (firstStep != null && firstStep.Status == WorkflowStepStatus.NotStarted)
        {
            firstStep.Start();
        }
    }

    public void Pause()
    {
        if (Status != WorkflowStatus.Running)
            throw new InvalidOperationException($"Cannot pause workflow in {Status} status");

        Status = WorkflowStatus.Paused;
        PausedAt = DateTime.UtcNow;
        UpdateTimestamp();
        AddDomainEvent(new WorkflowInstancePausedEvent(Id, PausedAt.Value));

        // Pause current running steps
        foreach (var step in _stepInstances.Where(s => s.Status == WorkflowStepStatus.Running))
        {
            step.Pause();
        }
    }

    public void Resume()
    {
        if (Status != WorkflowStatus.Paused)
            throw new InvalidOperationException($"Cannot resume workflow in {Status} status");

        Status = WorkflowStatus.Running;
        PausedAt = null;
        UpdateTimestamp();
        AddDomainEvent(new WorkflowInstanceResumedEvent(Id));

        // Resume paused steps
        foreach (var step in _stepInstances.Where(s => s.Status == WorkflowStepStatus.Paused))
        {
            step.Resume();
        }
    }

    public void Complete(WorkflowResult result)
    {
        if (Status == WorkflowStatus.Completed)
            throw new InvalidOperationException("Workflow is already completed");

        Status = WorkflowStatus.Completed;
        CompletedAt = DateTime.UtcNow;
        Result = result;
        Progress = 100;
        ActualDuration = StartedAt.HasValue ? CompletedAt.Value - StartedAt.Value : null;
        UpdateTimestamp();
        AddDomainEvent(new WorkflowInstanceCompletedEvent(Id, CompletedAt.Value, result));
    }

    public void Cancel(string reason = "")
    {
        if (Status == WorkflowStatus.Completed)
            throw new InvalidOperationException("Cannot cancel a completed workflow");

        Status = WorkflowStatus.Cancelled;
        CompletedAt = DateTime.UtcNow;
        Result = WorkflowResult.Cancelled(reason);
        ActualDuration = StartedAt.HasValue ? CompletedAt.Value - StartedAt.Value : null;
        UpdateTimestamp();
        AddDomainEvent(new WorkflowInstanceCancelledEvent(Id, reason));

        // Cancel all running or pending steps
        foreach (var step in _stepInstances.Where(s => s.Status == WorkflowStepStatus.Running || s.Status == WorkflowStepStatus.NotStarted))
        {
            step.Cancel("Workflow cancelled");
        }
    }

    public void Fail(string error)
    {
        Status = WorkflowStatus.Failed;
        CompletedAt = DateTime.UtcNow;
        Result = WorkflowResult.Failed(error);
        ActualDuration = StartedAt.HasValue ? CompletedAt.Value - StartedAt.Value : null;
        UpdateTimestamp();
        AddDomainEvent(new WorkflowInstanceFailedEvent(Id, error));
    }

    public WorkflowStepInstance? GetStepInstance(Guid stepInstanceId)
    {
        return _stepInstances.FirstOrDefault(s => s.Id == stepInstanceId);
    }

    public WorkflowStepInstance? GetCurrentStep()
    {
        return _stepInstances
            .Where(s => s.Status == WorkflowStepStatus.Running)
            .OrderBy(s => s.Order)
            .FirstOrDefault();
    }

    public WorkflowStepInstance? GetNextStep()
    {
        return _stepInstances
            .Where(s => s.Status == WorkflowStepStatus.NotStarted)
            .OrderBy(s => s.Order)
            .FirstOrDefault();
    }

    public void OnStepCompleted(Guid stepInstanceId)
    {
        var completedStep = _stepInstances.FirstOrDefault(s => s.Id == stepInstanceId);
        if (completedStep == null) return;

        UpdateProgress();

        // Start next step if available
        var nextStep = GetNextStep();
        if (nextStep != null)
        {
            nextStep.Start();
        }
        else
        {
            // Check if all required steps are completed
            var requiredSteps = _stepInstances.Where(s => s.IsRequired);
            if (requiredSteps.All(s => s.Status == WorkflowStepStatus.Completed))
            {
                Complete(WorkflowResult.Success());
            }
        }
    }

    public void OnStepFailed(Guid stepInstanceId, string error)
    {
        var failedStep = _stepInstances.FirstOrDefault(s => s.Id == stepInstanceId);
        if (failedStep?.IsRequired == true)
        {
            Fail($"Required step '{failedStep.Name}' failed: {error}");
        }
        else
        {
            UpdateProgress();
            // Continue with next step for optional steps
            var nextStep = GetNextStep();
            nextStep?.Start();
        }
    }

    private void UpdateProgress()
    {
        var totalSteps = _stepInstances.Count;
        if (totalSteps == 0)
        {
            Progress = 0;
            return;
        }

        var completedSteps = _stepInstances.Count(s => s.Status == WorkflowStepStatus.Completed);
        Progress = (decimal)completedSteps / totalSteps * 100;
        UpdateTimestamp();
    }

    public void SetContextValue(string key, object value)
    {
        _context[key] = value;
        UpdateTimestamp();
    }

    public T? GetContextValue<T>(string key)
    {
        if (_context.TryGetValue(key, out var value) && value is T typedValue)
        {
            return typedValue;
        }
        return default;
    }

    public void AddNotes(string notes)
    {
        Notes = string.IsNullOrEmpty(Notes) ? notes : $"{Notes}\n{notes}";
        UpdateTimestamp();
    }

    public void UpdateConfiguration(string configuration)
    {
        Configuration = configuration;
        UpdateTimestamp();
    }
}

public class WorkflowStepInstance : BaseEntity
{
    private readonly Dictionary<string, object> _stepContext = new();

    public Guid WorkflowInstanceId { get; private set; }
    public Guid StepTemplateId { get; private set; }
    public string Name { get; private set; }
    public string Description { get; private set; }
    public WorkflowStepType Type { get; private set; }
    public int Order { get; private set; }
    public bool IsRequired { get; private set; }
    public WorkflowStepStatus Status { get; private set; }
    public DateTime? StartedAt { get; private set; }
    public DateTime? CompletedAt { get; private set; }
    public DateTime? PausedAt { get; private set; }
    public TimeSpan? ActualDuration { get; private set; }
    public string? Configuration { get; private set; }
    public string? Result { get; private set; }
    public string? Error { get; private set; }
    public string? Notes { get; private set; }
    public IReadOnlyDictionary<string, object> StepContext => _stepContext.AsReadOnly();

    private WorkflowStepInstance(
        Guid workflowInstanceId,
        Guid stepTemplateId,
        string name,
        string description,
        WorkflowStepType type,
        int order,
        bool isRequired,
        string? configuration)
    {
        WorkflowInstanceId = workflowInstanceId;
        StepTemplateId = stepTemplateId;
        Name = name;
        Description = description;
        Type = type;
        Order = order;
        IsRequired = isRequired;
        Configuration = configuration;
        Status = WorkflowStepStatus.NotStarted;
    }

    public static WorkflowStepInstance CreateFromTemplate(Guid workflowInstanceId, WorkflowStepTemplate template)
    {
        return new WorkflowStepInstance(
            workflowInstanceId,
            template.Id,
            template.Name,
            template.Description,
            template.Type,
            template.Order,
            template.IsRequired,
            template.Configuration);
    }

    public void Start()
    {
        if (Status != WorkflowStepStatus.NotStarted && Status != WorkflowStepStatus.Paused)
            throw new InvalidOperationException($"Cannot start step in {Status} status");

        Status = WorkflowStepStatus.Running;
        StartedAt = DateTime.UtcNow;
        UpdateTimestamp();
    }

    public void Pause()
    {
        if (Status != WorkflowStepStatus.Running)
            throw new InvalidOperationException($"Cannot pause step in {Status} status");

        Status = WorkflowStepStatus.Paused;
        PausedAt = DateTime.UtcNow;
        UpdateTimestamp();
    }

    public void Resume()
    {
        if (Status != WorkflowStepStatus.Paused)
            throw new InvalidOperationException($"Cannot resume step in {Status} status");

        Status = WorkflowStepStatus.Running;
        PausedAt = null;
        UpdateTimestamp();
    }

    public void Complete(string? result = null)
    {
        if (Status != WorkflowStepStatus.Running)
            throw new InvalidOperationException($"Cannot complete step in {Status} status");

        Status = WorkflowStepStatus.Completed;
        CompletedAt = DateTime.UtcNow;
        Result = result;
        ActualDuration = StartedAt.HasValue ? CompletedAt.Value - StartedAt.Value : null;
        UpdateTimestamp();
    }

    public void Fail(string error)
    {
        if (Status == WorkflowStepStatus.Completed)
            throw new InvalidOperationException("Cannot fail a completed step");

        Status = WorkflowStepStatus.Failed;
        CompletedAt = DateTime.UtcNow;
        Error = error;
        ActualDuration = StartedAt.HasValue ? CompletedAt.Value - StartedAt.Value : null;
        UpdateTimestamp();
    }

    public void Cancel(string reason = "")
    {
        if (Status == WorkflowStepStatus.Completed)
            throw new InvalidOperationException("Cannot cancel a completed step");

        Status = WorkflowStepStatus.Cancelled;
        CompletedAt = DateTime.UtcNow;
        Error = reason;
        ActualDuration = StartedAt.HasValue ? CompletedAt.Value - StartedAt.Value : null;
        UpdateTimestamp();
    }

    public void Skip(string reason = "")
    {
        if (IsRequired)
            throw new InvalidOperationException("Cannot skip a required step");

        Status = WorkflowStepStatus.Skipped;
        CompletedAt = DateTime.UtcNow;
        Notes = reason;
        UpdateTimestamp();
    }

    public void SetStepContextValue(string key, object value)
    {
        _stepContext[key] = value;
        UpdateTimestamp();
    }

    public T? GetStepContextValue<T>(string key)
    {
        if (_stepContext.TryGetValue(key, out var value) && value is T typedValue)
        {
            return typedValue;
        }
        return default;
    }

    public void AddNotes(string notes)
    {
        Notes = string.IsNullOrEmpty(Notes) ? notes : $"{Notes}\n{notes}";
        UpdateTimestamp();
    }
}

// Domain Events
public record WorkflowInstanceCreatedEvent(Guid WorkflowInstanceId, Guid WorkflowTemplateId, string Name) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record WorkflowInstanceStartedEvent(Guid WorkflowInstanceId, DateTime StartedAt) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record WorkflowInstancePausedEvent(Guid WorkflowInstanceId, DateTime PausedAt) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record WorkflowInstanceResumedEvent(Guid WorkflowInstanceId) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record WorkflowInstanceCompletedEvent(Guid WorkflowInstanceId, DateTime CompletedAt, WorkflowResult Result) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record WorkflowInstanceCancelledEvent(Guid WorkflowInstanceId, string Reason) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record WorkflowInstanceFailedEvent(Guid WorkflowInstanceId, string Error) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}