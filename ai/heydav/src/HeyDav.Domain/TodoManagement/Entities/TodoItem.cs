using HeyDav.Domain.Common.Base;
using HeyDav.Domain.TodoManagement.Enums;
using HeyDav.Domain.TodoManagement.ValueObjects;

namespace HeyDav.Domain.TodoManagement.Entities;

public class TodoItem : AggregateRoot
{
    private readonly List<Guid> _dependencyIds = new();
    private readonly List<string> _tags = new();

    public string Title { get; private set; }
    public string? Description { get; private set; }
    public Priority Priority { get; private set; }
    public TodoStatus Status { get; private set; }
    public DateTime? DueDate { get; private set; }
    public DateTime? ScheduledDate { get; private set; }
    public DateTime? CompletedDate { get; private set; }
    public TimeSpan? EstimatedDuration { get; private set; }
    public TimeSpan? ActualDuration { get; private set; }
    public RecurrencePattern RecurrencePattern { get; private set; }
    public Guid? CategoryId { get; private set; }
    public Guid? ParentId { get; private set; }
    public Guid? GoalId { get; private set; }
    public int? EnergyLevel { get; private set; } // 1-5 scale
    public IReadOnlyList<Guid> DependencyIds => _dependencyIds.AsReadOnly();
    public IReadOnlyList<string> Tags => _tags.AsReadOnly();

    private TodoItem(
        string title,
        Priority priority = Priority.Medium,
        string? description = null,
        DateTime? dueDate = null,
        TimeSpan? estimatedDuration = null)
    {
        Title = title;
        Priority = priority;
        Description = description;
        DueDate = dueDate;
        EstimatedDuration = estimatedDuration;
        Status = TodoStatus.NotStarted;
        RecurrencePattern = RecurrencePattern.None();
    }

    public static TodoItem Create(
        string title,
        Priority priority = Priority.Medium,
        string? description = null,
        DateTime? dueDate = null,
        TimeSpan? estimatedDuration = null)
    {
        if (string.IsNullOrWhiteSpace(title))
            throw new ArgumentException("Title cannot be empty", nameof(title));

        var todo = new TodoItem(title, priority, description, dueDate, estimatedDuration);
        todo.AddDomainEvent(new TodoCreatedEvent(todo.Id, title));
        return todo;
    }

    public void UpdateTitle(string title)
    {
        if (string.IsNullOrWhiteSpace(title))
            throw new ArgumentException("Title cannot be empty", nameof(title));

        Title = title;
        UpdateTimestamp();
    }

    public void UpdateDescription(string? description)
    {
        Description = description;
        UpdateTimestamp();
    }

    public void UpdatePriority(Priority priority)
    {
        Priority = priority;
        UpdateTimestamp();
        AddDomainEvent(new TodoPriorityChangedEvent(Id, priority));
    }

    public void UpdateDueDate(DateTime? dueDate)
    {
        DueDate = dueDate;
        UpdateTimestamp();
    }

    public void Schedule(DateTime scheduledDate)
    {
        ScheduledDate = scheduledDate;
        UpdateTimestamp();
        AddDomainEvent(new TodoScheduledEvent(Id, scheduledDate));
    }

    public void Start()
    {
        if (Status != TodoStatus.NotStarted)
            throw new InvalidOperationException("Can only start a todo that hasn't been started");

        Status = TodoStatus.InProgress;
        UpdateTimestamp();
        AddDomainEvent(new TodoStartedEvent(Id));
    }

    public void Complete(TimeSpan? actualDuration = null)
    {
        if (Status == TodoStatus.Completed)
            throw new InvalidOperationException("Todo is already completed");

        Status = TodoStatus.Completed;
        CompletedDate = DateTime.UtcNow;
        ActualDuration = actualDuration;
        UpdateTimestamp();
        AddDomainEvent(new TodoCompletedEvent(Id, CompletedDate.Value));
    }

    public void Cancel()
    {
        if (Status == TodoStatus.Completed)
            throw new InvalidOperationException("Cannot cancel a completed todo");

        Status = TodoStatus.Cancelled;
        UpdateTimestamp();
    }

    public void Defer(DateTime newDate)
    {
        if (Status == TodoStatus.Completed)
            throw new InvalidOperationException("Cannot defer a completed todo");

        Status = TodoStatus.Deferred;
        DueDate = newDate;
        UpdateTimestamp();
    }

    public void SetRecurrence(RecurrencePattern pattern)
    {
        RecurrencePattern = pattern ?? throw new ArgumentNullException(nameof(pattern));
        UpdateTimestamp();
    }

    public void AssignToCategory(Guid categoryId)
    {
        CategoryId = categoryId;
        UpdateTimestamp();
    }

    public void AssignToGoal(Guid goalId)
    {
        GoalId = goalId;
        UpdateTimestamp();
    }

    public void SetParent(Guid parentId)
    {
        if (parentId == Id)
            throw new InvalidOperationException("A todo cannot be its own parent");

        ParentId = parentId;
        UpdateTimestamp();
    }

    public void AddDependency(Guid dependencyId)
    {
        if (dependencyId == Id)
            throw new InvalidOperationException("A todo cannot depend on itself");

        if (!_dependencyIds.Contains(dependencyId))
        {
            _dependencyIds.Add(dependencyId);
            UpdateTimestamp();
        }
    }

    public void RemoveDependency(Guid dependencyId)
    {
        if (_dependencyIds.Remove(dependencyId))
        {
            UpdateTimestamp();
        }
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

    public void SetEnergyLevel(int energyLevel)
    {
        if (energyLevel < 1 || energyLevel > 5)
            throw new ArgumentOutOfRangeException(nameof(energyLevel), "Energy level must be between 1 and 5");

        EnergyLevel = energyLevel;
        UpdateTimestamp();
    }

    public TodoItem? CreateNextRecurrence()
    {
        if (RecurrencePattern.Type == RecurrenceType.None || Status != TodoStatus.Completed)
            return null;

        var nextDate = RecurrencePattern.GetNextOccurrence(CompletedDate ?? DateTime.UtcNow);
        if (nextDate == null)
            return null;

        var nextTodo = Create(Title, Priority, Description, nextDate, EstimatedDuration);
        nextTodo.SetRecurrence(RecurrencePattern);
        nextTodo.CategoryId = CategoryId;
        nextTodo.GoalId = GoalId;
        nextTodo.EnergyLevel = EnergyLevel;
        
        foreach (var tag in _tags)
        {
            nextTodo.AddTag(tag);
        }

        return nextTodo;
    }
}

// Domain Events
public record TodoCreatedEvent(Guid TodoId, string Title) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record TodoStartedEvent(Guid TodoId) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record TodoCompletedEvent(Guid TodoId, DateTime CompletedDate) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record TodoScheduledEvent(Guid TodoId, DateTime ScheduledDate) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record TodoPriorityChangedEvent(Guid TodoId, Priority NewPriority) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}