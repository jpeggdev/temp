namespace HeyDav.Domain.Common.Base;

public interface IDomainEvent
{
    DateTime OccurredOn { get; }
}