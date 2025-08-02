using Microsoft.EntityFrameworkCore;
using HeyDav.Domain.TodoManagement.Entities;
using HeyDav.Domain.TodoManagement.Enums;
using HeyDav.Domain.TodoManagement.Interfaces;

namespace HeyDav.Infrastructure.Persistence.Repositories;

public class TodoRepository(HeyDavDbContext context) : Repository<TodoItem>(context), ITodoRepository
{
    public async Task<IReadOnlyList<TodoItem>> GetIncompleteTasksAsync(CancellationToken cancellationToken = default)
    {
        return await _dbSet
            .Where(t => t.Status != TodoStatus.Completed && t.Status != TodoStatus.Cancelled)
            .OrderBy(t => t.Priority)
            .ThenBy(t => t.DueDate)
            .ToListAsync(cancellationToken);
    }

    public async Task<IReadOnlyList<TodoItem>> GetTasksByDateRangeAsync(DateTime start, DateTime end, CancellationToken cancellationToken = default)
    {
        return await _dbSet
            .Where(t => t.DueDate >= start && t.DueDate <= end)
            .OrderBy(t => t.DueDate)
            .ThenBy(t => t.Priority)
            .ToListAsync(cancellationToken);
    }

    public async Task<IReadOnlyList<TodoItem>> GetTasksByCategoryAsync(Guid categoryId, CancellationToken cancellationToken = default)
    {
        return await _dbSet
            .Where(t => t.CategoryId == categoryId)
            .OrderBy(t => t.Priority)
            .ThenBy(t => t.DueDate)
            .ToListAsync(cancellationToken);
    }

    public async Task<IReadOnlyList<TodoItem>> GetTasksByGoalAsync(Guid goalId, CancellationToken cancellationToken = default)
    {
        return await _dbSet
            .Where(t => t.GoalId == goalId)
            .OrderBy(t => t.Priority)
            .ThenBy(t => t.DueDate)
            .ToListAsync(cancellationToken);
    }

    public async Task<IReadOnlyList<TodoItem>> GetTasksByStatusAsync(TodoStatus status, CancellationToken cancellationToken = default)
    {
        return await _dbSet
            .Where(t => t.Status == status)
            .OrderBy(t => t.Priority)
            .ThenBy(t => t.DueDate)
            .ToListAsync(cancellationToken);
    }

    public async Task<IReadOnlyList<TodoItem>> GetTasksByPriorityAsync(Priority priority, CancellationToken cancellationToken = default)
    {
        return await _dbSet
            .Where(t => t.Priority == priority)
            .OrderBy(t => t.DueDate)
            .ToListAsync(cancellationToken);
    }

    public async Task<IReadOnlyList<TodoItem>> GetOverdueTasksAsync(CancellationToken cancellationToken = default)
    {
        var now = DateTime.UtcNow;
        return await _dbSet
            .Where(t => t.DueDate < now && 
                       t.Status != TodoStatus.Completed && 
                       t.Status != TodoStatus.Cancelled)
            .OrderBy(t => t.DueDate)
            .ToListAsync(cancellationToken);
    }

    public async Task<IReadOnlyList<TodoItem>> GetTasksScheduledForDateAsync(DateTime date, CancellationToken cancellationToken = default)
    {
        var startOfDay = date.Date;
        var endOfDay = startOfDay.AddDays(1);

        return await _dbSet
            .Where(t => t.ScheduledDate >= startOfDay && t.ScheduledDate < endOfDay)
            .OrderBy(t => t.ScheduledDate)
            .ThenBy(t => t.Priority)
            .ToListAsync(cancellationToken);
    }

    public async Task<IReadOnlyList<TodoItem>> GetSubtasksAsync(Guid parentId, CancellationToken cancellationToken = default)
    {
        return await _dbSet
            .Where(t => t.ParentId == parentId)
            .OrderBy(t => t.Priority)
            .ThenBy(t => t.CreatedAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<IReadOnlyList<TodoItem>> GetTasksWithDependenciesAsync(Guid taskId, CancellationToken cancellationToken = default)
    {
        // This is a simplified implementation
        // In a real scenario, you might need a more complex query to handle JSON arrays
        return await _dbSet
            .Where(t => t.DependencyIds.Contains(taskId))
            .ToListAsync(cancellationToken);
    }
}