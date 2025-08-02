using Microsoft.AspNetCore.Mvc;
using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.TodoManagement.Commands;
using HeyDav.Application.TodoManagement.Queries;
using HeyDav.Domain.TodoManagement.Enums;

namespace HeyDav.Api.Controllers;

[ApiController]
[Route("api/[controller]")]
public class TodoController : ControllerBase
{
    private readonly IMediator _mediator;
    private readonly ILogger<TodoController> _logger;

    public TodoController(IMediator mediator, ILogger<TodoController> logger)
    {
        _mediator = mediator;
        _logger = logger;
    }

    [HttpGet]
    public async Task<ActionResult> GetTodos()
    {
        try
        {
            var todos = await _mediator.Send(new GetTodosQuery());
            return Ok(todos);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error retrieving todos");
            return StatusCode(500, "Failed to retrieve todos");
        }
    }

    [HttpPost]
    public async Task<ActionResult> CreateTodo([FromBody] CreateTodoRequest request)
    {
        try
        {
            var priority = Enum.TryParse<Priority>(request.Priority, true, out var parsedPriority) 
                ? parsedPriority 
                : Priority.Medium;

            var command = new CreateTodoCommand(
                request.Title,
                priority,
                request.DueDate,
                request.Description,
                request.Tags,
                request.CategoryName);

            var todoId = await _mediator.Send(command);
            return CreatedAtAction(nameof(GetTodo), new { id = todoId }, new { Id = todoId });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error creating todo");
            return StatusCode(500, "Failed to create todo");
        }
    }

    [HttpGet("{id}")]
    public async Task<ActionResult> GetTodo(Guid id)
    {
        try
        {
            var todos = await _mediator.Send(new GetTodosQuery());
            var todo = todos.FirstOrDefault(t => t.Id == id);
            
            if (todo == null)
                return NotFound();
                
            return Ok(todo);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error retrieving todo {TodoId}", id);
            return StatusCode(500, "Failed to retrieve todo");
        }
    }

    [HttpPut("{id}")]
    public async Task<ActionResult> UpdateTodo(Guid id, [FromBody] UpdateTodoRequest request)
    {
        try
        {
            // Note: Update commands would need to be implemented in the Application layer
            // For now, returning a placeholder response
            return Ok(new { Message = "Todo update functionality needs to be implemented" });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error updating todo {TodoId}", id);
            return StatusCode(500, "Failed to update todo");
        }
    }

    [HttpDelete("{id}")]
    public async Task<ActionResult> DeleteTodo(Guid id)
    {
        try
        {
            // Note: Delete commands would need to be implemented in the Application layer
            // For now, returning a placeholder response
            return Ok(new { Message = "Todo delete functionality needs to be implemented" });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error deleting todo {TodoId}", id);
            return StatusCode(500, "Failed to delete todo");
        }
    }

    [HttpPost("{id}/complete")]
    public async Task<ActionResult> CompleteTodo(Guid id)
    {
        try
        {
            // Note: Complete commands would need to be implemented in the Application layer
            // For now, returning a placeholder response
            return Ok(new { Message = "Todo completion functionality needs to be implemented" });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error completing todo {TodoId}", id);
            return StatusCode(500, "Failed to complete todo");
        }
    }
}

public class CreateTodoRequest
{
    public string Title { get; set; } = string.Empty;
    public string Priority { get; set; } = "Medium";
    public DateTime? DueDate { get; set; }
    public string? Description { get; set; }
    public List<string>? Tags { get; set; }
    public string? CategoryName { get; set; }
}

public class UpdateTodoRequest
{
    public string? Title { get; set; }
    public string? Priority { get; set; }
    public DateTime? DueDate { get; set; }
    public string? Description { get; set; }
    public List<string>? Tags { get; set; }
    public string? CategoryName { get; set; }
    public string? Status { get; set; }
}