using HeyDav.Domain.Common.Base;

namespace HeyDav.Domain.Goals.Entities;

public class Goal : AggregateRoot
{
    private readonly List<Milestone> _milestones = new();
    private readonly List<string> _tags = new();

    public string Title { get; private set; }
    public string? Description { get; private set; }
    public GoalType Type { get; private set; }
    public GoalStatus Status { get; private set; }
    public DateTime? TargetDate { get; private set; }
    public DateTime? AchievedDate { get; private set; }
    public decimal Progress { get; private set; } // 0-100
    public string? Metrics { get; private set; } // JSON for flexible metrics
    public Guid? ParentGoalId { get; private set; }
    public GoalPriority Priority { get; private set; }
    public IReadOnlyList<Milestone> Milestones => _milestones.AsReadOnly();
    public IReadOnlyList<string> Tags => _tags.AsReadOnly();

    private Goal(string title, GoalType type, DateTime? targetDate = null)
    {
        Title = title;
        Type = type;
        TargetDate = targetDate;
        Status = GoalStatus.NotStarted;
        Progress = 0;
        Priority = GoalPriority.Medium;
    }

    public static Goal Create(string title, GoalType type, string? description = null, DateTime? targetDate = null)
    {
        if (string.IsNullOrWhiteSpace(title))
            throw new ArgumentException("Goal title cannot be empty", nameof(title));

        var goal = new Goal(title, type, targetDate)
        {
            Description = description
        };

        goal.AddDomainEvent(new GoalCreatedEvent(goal.Id, title));
        return goal;
    }

    public void UpdateDetails(string title, string? description, DateTime? targetDate)
    {
        if (string.IsNullOrWhiteSpace(title))
            throw new ArgumentException("Goal title cannot be empty", nameof(title));

        Title = title;
        Description = description;
        TargetDate = targetDate;
        UpdateTimestamp();
    }

    public void SetPriority(GoalPriority priority)
    {
        Priority = priority;
        UpdateTimestamp();
    }

    public void Start()
    {
        if (Status != GoalStatus.NotStarted)
            throw new InvalidOperationException("Goal has already been started");

        Status = GoalStatus.InProgress;
        UpdateTimestamp();
        AddDomainEvent(new GoalStartedEvent(Id));
    }

    public void UpdateProgress(decimal progress)
    {
        if (progress < 0 || progress > 100)
            throw new ArgumentOutOfRangeException(nameof(progress), "Progress must be between 0 and 100");

        Progress = progress;
        UpdateTimestamp();

        if (progress >= 100 && Status != GoalStatus.Achieved)
        {
            Achieve();
        }
    }

    public void Achieve()
    {
        Status = GoalStatus.Achieved;
        AchievedDate = DateTime.UtcNow;
        Progress = 100;
        UpdateTimestamp();
        AddDomainEvent(new GoalAchievedEvent(Id, AchievedDate.Value));
    }

    public void Abandon()
    {
        if (Status == GoalStatus.Achieved)
            throw new InvalidOperationException("Cannot abandon an achieved goal");

        Status = GoalStatus.Abandoned;
        UpdateTimestamp();
    }

    public void SetParentGoal(Guid parentGoalId)
    {
        if (parentGoalId == Id)
            throw new InvalidOperationException("A goal cannot be its own parent");

        ParentGoalId = parentGoalId;
        UpdateTimestamp();
    }

    public Milestone AddMilestone(string title, DateTime? targetDate = null, decimal targetProgress = 0)
    {
        var milestone = Milestone.Create(Id, title, targetDate, targetProgress);
        _milestones.Add(milestone);
        UpdateTimestamp();
        return milestone;
    }

    public void UpdateMilestone(Guid milestoneId, string title, DateTime? targetDate, decimal targetProgress)
    {
        var milestone = _milestones.FirstOrDefault(m => m.Id == milestoneId);
        if (milestone == null)
            throw new InvalidOperationException("Milestone not found");

        milestone.Update(title, targetDate, targetProgress);
        UpdateTimestamp();
    }

    public void CompleteMilestone(Guid milestoneId)
    {
        var milestone = _milestones.FirstOrDefault(m => m.Id == milestoneId);
        if (milestone == null)
            throw new InvalidOperationException("Milestone not found");

        milestone.Complete();
        UpdateTimestamp();
        RecalculateProgress();
    }

    private void RecalculateProgress()
    {
        if (!_milestones.Any())
            return;

        var completedMilestones = _milestones.Count(m => m.IsCompleted);
        var totalMilestones = _milestones.Count;
        
        UpdateProgress((decimal)completedMilestones / totalMilestones * 100);
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

    public void SetMetrics(string metrics)
    {
        Metrics = metrics;
        UpdateTimestamp();
    }
}

public class Milestone : BaseEntity
{
    public Guid GoalId { get; private set; }
    public string Title { get; private set; }
    public DateTime? TargetDate { get; private set; }
    public DateTime? CompletedDate { get; private set; }
    public decimal TargetProgress { get; private set; }
    public bool IsCompleted { get; private set; }

    private Milestone(Guid goalId, string title, DateTime? targetDate, decimal targetProgress)
    {
        GoalId = goalId;
        Title = title;
        TargetDate = targetDate;
        TargetProgress = targetProgress;
        IsCompleted = false;
    }

    public static Milestone Create(Guid goalId, string title, DateTime? targetDate = null, decimal targetProgress = 0)
    {
        if (string.IsNullOrWhiteSpace(title))
            throw new ArgumentException("Milestone title cannot be empty", nameof(title));

        return new Milestone(goalId, title, targetDate, targetProgress);
    }

    public void Update(string title, DateTime? targetDate, decimal targetProgress)
    {
        Title = title;
        TargetDate = targetDate;
        TargetProgress = targetProgress;
        UpdateTimestamp();
    }

    public void Complete()
    {
        IsCompleted = true;
        CompletedDate = DateTime.UtcNow;
        UpdateTimestamp();
    }
}

public enum GoalType
{
    Personal,
    Professional,
    Health,
    Financial,
    Educational,
    Social,
    Spiritual,
    Other
}

public enum GoalStatus
{
    NotStarted,
    InProgress,
    Achieved,
    Abandoned
}

public enum GoalPriority
{
    Low,
    Medium,
    High,
    Critical
}

// Domain Events
public record GoalCreatedEvent(Guid GoalId, string Title) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record GoalStartedEvent(Guid GoalId) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record GoalAchievedEvent(Guid GoalId, DateTime AchievedDate) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}