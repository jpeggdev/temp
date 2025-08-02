using HeyDav.Domain.Common.Base;
using HeyDav.Domain.AgentManagement.Enums;

namespace HeyDav.Domain.AgentManagement.Events;

public record AgentTaskCreatedEvent(Guid TaskId, string Title, TaskPriority Priority) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record AgentTaskScheduledEvent(Guid TaskId, DateTime ScheduledAt) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record AgentTaskStartedEvent(Guid TaskId, Guid AgentId) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record AgentTaskCompletedEvent(Guid TaskId, Guid AgentId, string? Result) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record AgentTaskFailedEvent(Guid TaskId, Guid? AgentId, string ErrorMessage) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record AgentTaskCancelledEvent(Guid TaskId) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record AgentTaskRetryEvent(Guid TaskId, int RetryCount) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record AgentTaskPriorityChangedEvent(Guid TaskId, TaskPriority NewPriority) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}