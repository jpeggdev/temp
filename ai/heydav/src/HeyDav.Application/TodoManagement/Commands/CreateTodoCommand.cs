using HeyDav.Application.Common.Interfaces;
using HeyDav.Domain.TodoManagement.Entities;
using HeyDav.Domain.TodoManagement.Enums;
using HeyDav.Domain.TodoManagement.Interfaces;

namespace HeyDav.Application.TodoManagement.Commands;

public record CreateTodoCommand(
    string Title,
    string? Description = null,
    Priority Priority = Priority.Medium,
    DateTime? DueDate = null,
    TimeSpan? EstimatedDuration = null,
    Guid? CategoryId = null,
    Guid? GoalId = null) : ICommand<Guid>;

public class CreateTodoCommandHandler(ITodoRepository repository) : ICommandHandler<CreateTodoCommand, Guid>
{
    private readonly ITodoRepository _repository = repository ?? throw new ArgumentNullException(nameof(repository));

    public async Task<Guid> Handle(CreateTodoCommand request, CancellationToken cancellationToken)
    {
        var todoItem = TodoItem.Create(
            request.Title,
            request.Priority,
            request.Description,
            request.DueDate,
            request.EstimatedDuration);

        if (request.CategoryId.HasValue)
        {
            todoItem.AssignToCategory(request.CategoryId.Value);
        }

        if (request.GoalId.HasValue)
        {
            todoItem.AssignToGoal(request.GoalId.Value);
        }

        await _repository.AddAsync(todoItem, cancellationToken);
        
        return todoItem.Id;
    }
}