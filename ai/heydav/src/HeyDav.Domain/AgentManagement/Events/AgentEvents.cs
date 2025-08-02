using HeyDav.Domain.Common.Base;
using HeyDav.Domain.AgentManagement.Enums;
using HeyDav.Domain.AgentManagement.ValueObjects;

namespace HeyDav.Domain.AgentManagement.Events;

public record AgentCreatedEvent(Guid AgentId, string Name, AgentType Type) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record AgentActivatedEvent(Guid AgentId) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record AgentDeactivatedEvent(Guid AgentId) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record AgentErrorEvent(Guid AgentId, string Error) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record AgentConfigurationUpdatedEvent(Guid AgentId, AgentConfiguration Configuration) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record TaskAssignedToAgentEvent(Guid AgentId, Guid TaskId) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record TaskCompletedByAgentEvent(Guid AgentId, Guid TaskId, TimeSpan ResponseTime) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record TaskFailedByAgentEvent(Guid AgentId, Guid TaskId, string Error) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}