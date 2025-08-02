using HeyDav.Domain.Common.Interfaces;
using HeyDav.Domain.TodoManagement.Entities;
using HeyDav.Domain.TodoManagement.Enums;

namespace HeyDav.Domain.TodoManagement.Interfaces;

public interface ITodoRepository : IRepository<TodoItem>
{
    Task<IReadOnlyList<TodoItem>> GetIncompleteTasksAsync(CancellationToken cancellationToken = default);
    Task<IReadOnlyList<TodoItem>> GetTasksByDateRangeAsync(DateTime start, DateTime end, CancellationToken cancellationToken = default);
    Task<IReadOnlyList<TodoItem>> GetTasksByCategoryAsync(Guid categoryId, CancellationToken cancellationToken = default);
    Task<IReadOnlyList<TodoItem>> GetTasksByGoalAsync(Guid goalId, CancellationToken cancellationToken = default);
    Task<IReadOnlyList<TodoItem>> GetTasksByStatusAsync(TodoStatus status, CancellationToken cancellationToken = default);
    Task<IReadOnlyList<TodoItem>> GetTasksByPriorityAsync(Priority priority, CancellationToken cancellationToken = default);
    Task<IReadOnlyList<TodoItem>> GetOverdueTasksAsync(CancellationToken cancellationToken = default);
    Task<IReadOnlyList<TodoItem>> GetTasksScheduledForDateAsync(DateTime date, CancellationToken cancellationToken = default);
    Task<IReadOnlyList<TodoItem>> GetSubtasksAsync(Guid parentId, CancellationToken cancellationToken = default);
    Task<IReadOnlyList<TodoItem>> GetTasksWithDependenciesAsync(Guid taskId, CancellationToken cancellationToken = default);
}