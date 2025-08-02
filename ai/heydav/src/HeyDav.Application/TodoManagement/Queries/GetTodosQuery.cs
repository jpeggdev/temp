using HeyDav.Application.Common.Interfaces;
using HeyDav.Domain.TodoManagement.Entities;
using HeyDav.Domain.TodoManagement.Interfaces;

namespace HeyDav.Application.TodoManagement.Queries;

public record GetTodosQuery() : IQuery<IReadOnlyList<TodoItem>>;

public class GetTodosQueryHandler(ITodoRepository repository) : IQueryHandler<GetTodosQuery, IReadOnlyList<TodoItem>>
{
    private readonly ITodoRepository _repository = repository ?? throw new ArgumentNullException(nameof(repository));

    public async Task<IReadOnlyList<TodoItem>> Handle(GetTodosQuery request, CancellationToken cancellationToken)
    {
        return await _repository.GetIncompleteTasksAsync(cancellationToken);
    }
}

public record GetTodosByDateQuery(DateTime Date) : IQuery<IReadOnlyList<TodoItem>>;

public class GetTodosByDateQueryHandler(ITodoRepository repository)
    : IQueryHandler<GetTodosByDateQuery, IReadOnlyList<TodoItem>>
{
    private readonly ITodoRepository _repository = repository ?? throw new ArgumentNullException(nameof(repository));

    public async Task<IReadOnlyList<TodoItem>> Handle(GetTodosByDateQuery request, CancellationToken cancellationToken)
    {
        return await _repository.GetTasksScheduledForDateAsync(request.Date, cancellationToken);
    }
}