using HeyDav.Domain.Common.Base;

namespace HeyDav.Domain.FinancialGoals.Entities;

public class FinancialGoal : AggregateRoot
{
    public string Name { get; private set; }
    public string? Description { get; private set; }
    public decimal TargetAmount { get; private set; }
    public decimal CurrentAmount { get; private set; }
    public DateTime? TargetDate { get; private set; }
    public FinancialGoalType Type { get; private set; }
    public FinancialGoalStatus Status { get; private set; }
    public string Currency { get; private set; }

    private FinancialGoal(string name, decimal targetAmount, FinancialGoalType type, string currency = "USD")
    {
        Name = name;
        TargetAmount = targetAmount;
        Type = type;
        Currency = currency;
        CurrentAmount = 0;
        Status = FinancialGoalStatus.Active;
    }

    public static FinancialGoal Create(
        string name,
        decimal targetAmount,
        FinancialGoalType type,
        string? description = null,
        DateTime? targetDate = null,
        string currency = "USD")
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("Goal name cannot be empty", nameof(name));

        if (targetAmount <= 0)
            throw new ArgumentException("Target amount must be positive", nameof(targetAmount));

        var goal = new FinancialGoal(name, targetAmount, type, currency)
        {
            Description = description,
            TargetDate = targetDate
        };

        return goal;
    }

    public void UpdateProgress(decimal newAmount)
    {
        if (newAmount < 0)
            throw new ArgumentException("Amount cannot be negative", nameof(newAmount));

        CurrentAmount = newAmount;
        UpdateTimestamp();

        if (CurrentAmount >= TargetAmount && Status == FinancialGoalStatus.Active)
        {
            Complete();
        }
    }

    public void Complete()
    {
        Status = FinancialGoalStatus.Completed;
        UpdateTimestamp();
        AddDomainEvent(new FinancialGoalCompletedEvent(Id, Name, CurrentAmount));
    }

    public decimal GetProgressPercentage()
    {
        return TargetAmount > 0 ? (CurrentAmount / TargetAmount) * 100 : 0;
    }
}

public enum FinancialGoalType
{
    Savings,
    Investment,
    DebtPayoff,
    Emergency,
    Retirement,
    Vacation,
    Purchase,
    Other
}

public enum FinancialGoalStatus
{
    Active,
    Completed,
    Paused,
    Cancelled
}

public record FinancialGoalCompletedEvent(Guid GoalId, string Name, decimal Amount) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}