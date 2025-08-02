using HeyDav.Domain.Common.Base;
using HeyDav.Domain.AgentManagement.Enums;
using HeyDav.Domain.AgentManagement.Events;

namespace HeyDav.Domain.AgentManagement.Entities;

public class AgentTask : AggregateRoot
{
    private readonly List<string> _requiredCapabilities = new();
    private readonly Dictionary<string, object> _parameters = new();

    public string Title { get; private set; }
    public string? Description { get; private set; }
    public TaskPriority Priority { get; private set; }
    public AgentTaskStatus Status { get; private set; }
    public Guid? AssignedAgentId { get; private set; }
    public DateTime? ScheduledAt { get; private set; }
    public DateTime? StartedAt { get; private set; }
    public DateTime? CompletedAt { get; private set; }
    public DateTime? DueDate { get; private set; }
    public string? Result { get; private set; }
    public string? ErrorMessage { get; private set; }
    public int RetryCount { get; private set; }
    public int MaxRetries { get; private set; }
    public IReadOnlyList<string> RequiredCapabilities => _requiredCapabilities.AsReadOnly();
    public IReadOnlyDictionary<string, object> Parameters => _parameters.AsReadOnly();

    private AgentTask()
    {
        // EF Constructor
        Title = string.Empty;
        Priority = TaskPriority.Normal;
        MaxRetries = 3;
        Status = AgentTaskStatus.Pending;
        RetryCount = 0;
    }

    private AgentTask(
        string title,
        TaskPriority priority,
        string? description = null,
        DateTime? dueDate = null,
        int maxRetries = 3)
    {
        Title = title;
        Priority = priority;
        Description = description;
        DueDate = dueDate;
        MaxRetries = maxRetries;
        Status = AgentTaskStatus.Pending;
        RetryCount = 0;
    }

    public static AgentTask Create(
        string title,
        TaskPriority priority = TaskPriority.Normal,
        string? description = null,
        DateTime? dueDate = null,
        int maxRetries = 3)
    {
        if (string.IsNullOrWhiteSpace(title))
            throw new ArgumentException("Task title cannot be empty", nameof(title));

        if (maxRetries < 0)
            throw new ArgumentException("Max retries cannot be negative", nameof(maxRetries));

        var task = new AgentTask(title, priority, description, dueDate, maxRetries);
        task.AddDomainEvent(new AgentTaskCreatedEvent(task.Id, title, priority));
        return task;
    }

    public void UpdateTitle(string title)
    {
        if (string.IsNullOrWhiteSpace(title))
            throw new ArgumentException("Task title cannot be empty", nameof(title));

        Title = title;
        UpdateTimestamp();
    }

    public void UpdateDescription(string? description)
    {
        Description = description;
        UpdateTimestamp();
    }

    public void UpdatePriority(TaskPriority priority)
    {
        Priority = priority;
        UpdateTimestamp();
        AddDomainEvent(new AgentTaskPriorityChangedEvent(Id, priority));
    }

    public void UpdateDueDate(DateTime? dueDate)
    {
        DueDate = dueDate;
        UpdateTimestamp();
    }

    public void AddRequiredCapability(string capability)
    {
        if (string.IsNullOrWhiteSpace(capability))
            throw new ArgumentException("Capability cannot be empty", nameof(capability));

        if (!_requiredCapabilities.Contains(capability))
        {
            _requiredCapabilities.Add(capability);
            UpdateTimestamp();
        }
    }

    public void RemoveRequiredCapability(string capability)
    {
        if (_requiredCapabilities.Remove(capability))
        {
            UpdateTimestamp();
        }
    }

    public void AddParameter(string key, object value)
    {
        if (string.IsNullOrWhiteSpace(key))
            throw new ArgumentException("Parameter key cannot be empty", nameof(key));

        _parameters[key] = value;
        UpdateTimestamp();
    }

    public void RemoveParameter(string key)
    {
        if (_parameters.Remove(key))
        {
            UpdateTimestamp();
        }
    }

    public void Schedule(DateTime scheduledAt)
    {
        if (Status != AgentTaskStatus.Pending)
            throw new InvalidOperationException("Only pending tasks can be scheduled");

        ScheduledAt = scheduledAt;
        UpdateTimestamp();
        AddDomainEvent(new AgentTaskScheduledEvent(Id, scheduledAt));
    }

    public void AssignToAgent(Guid agentId)
    {
        if (Status != AgentTaskStatus.Pending)
            throw new InvalidOperationException("Only pending tasks can be assigned");

        AssignedAgentId = agentId;
        Status = AgentTaskStatus.Assigned;
        UpdateTimestamp();
    }

    public void Start()
    {
        if (Status != AgentTaskStatus.Assigned)
            throw new InvalidOperationException("Only assigned tasks can be started");

        Status = AgentTaskStatus.InProgress;
        StartedAt = DateTime.UtcNow;
        UpdateTimestamp();
        AddDomainEvent(new AgentTaskStartedEvent(Id, AssignedAgentId!.Value));
    }

    public void Complete(string? result = null)
    {
        if (Status != AgentTaskStatus.InProgress)
            throw new InvalidOperationException("Only in-progress tasks can be completed");

        Status = AgentTaskStatus.Completed;
        CompletedAt = DateTime.UtcNow;
        Result = result;
        UpdateTimestamp();
        AddDomainEvent(new AgentTaskCompletedEvent(Id, AssignedAgentId!.Value, result));
    }

    public void Fail(string errorMessage)
    {
        if (string.IsNullOrWhiteSpace(errorMessage))
            throw new ArgumentException("Error message cannot be empty", nameof(errorMessage));

        Status = AgentTaskStatus.Failed;
        ErrorMessage = errorMessage;
        UpdateTimestamp();
        AddDomainEvent(new AgentTaskFailedEvent(Id, AssignedAgentId, errorMessage));
    }

    public void Cancel()
    {
        if (Status == AgentTaskStatus.Completed)
            throw new InvalidOperationException("Cannot cancel a completed task");

        Status = AgentTaskStatus.Cancelled;
        UpdateTimestamp();
        AddDomainEvent(new AgentTaskCancelledEvent(Id));
    }

    public bool CanRetry()
    {
        return Status == AgentTaskStatus.Failed && RetryCount < MaxRetries;
    }

    public void Retry()
    {
        if (!CanRetry())
            throw new InvalidOperationException("Task cannot be retried");

        RetryCount++;
        Status = AgentTaskStatus.Pending;
        AssignedAgentId = null;
        ErrorMessage = null;
        UpdateTimestamp();
        AddDomainEvent(new AgentTaskRetryEvent(Id, RetryCount));
    }

    public bool IsOverdue()
    {
        return DueDate.HasValue && DateTime.UtcNow > DueDate.Value && Status != AgentTaskStatus.Completed;
    }

    public TimeSpan? GetExecutionTime()
    {
        if (StartedAt.HasValue && CompletedAt.HasValue)
        {
            return CompletedAt.Value - StartedAt.Value;
        }
        return null;
    }
}