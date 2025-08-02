using Microsoft.AspNetCore.Mvc;
using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.AgentManagement.Commands;
using HeyDav.Application.AgentManagement.Queries;
using HeyDav.Infrastructure.Services;

namespace HeyDav.Api.Controllers;

[ApiController]
[Route("api/[controller]")]
public class AgentController : ControllerBase
{
    private readonly IMediator _mediator;
    private readonly IAgentOrchestrator _agentOrchestrator;
    private readonly ILogger<AgentController> _logger;

    public AgentController(
        IMediator mediator, 
        IAgentOrchestrator agentOrchestrator,
        ILogger<AgentController> logger)
    {
        _mediator = mediator;
        _agentOrchestrator = agentOrchestrator;
        _logger = logger;
    }

    [HttpGet]
    public async Task<ActionResult> GetAgents()
    {
        try
        {
            var agents = await _mediator.Send(new GetAgentsQuery());
            return Ok(agents);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error retrieving agents");
            return StatusCode(500, "Failed to retrieve agents");
        }
    }

    [HttpPost]
    public async Task<ActionResult> CreateAgent([FromBody] CreateAgentRequest request)
    {
        try
        {
            var command = new CreateAgentCommand(
                request.Name,
                request.Type,
                request.Configuration,
                request.Description);

            var agentId = await _mediator.Send(command);
            return CreatedAtAction(nameof(GetAgent), new { id = agentId }, new { Id = agentId });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error creating agent");
            return StatusCode(500, "Failed to create agent");
        }
    }

    [HttpGet("{id}")]
    public async Task<ActionResult> GetAgent(Guid id)
    {
        try
        {
            var agents = await _mediator.Send(new GetAgentsQuery());
            var agent = agents.FirstOrDefault(a => a.Id == id);
            
            if (agent == null)
                return NotFound();
                
            return Ok(agent);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error retrieving agent {AgentId}", id);
            return StatusCode(500, "Failed to retrieve agent");
        }
    }

    [HttpGet("tasks")]
    public async Task<ActionResult> GetTasks()
    {
        try
        {
            var tasks = await _mediator.Send(new GetTasksQuery());
            return Ok(tasks);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error retrieving tasks");
            return StatusCode(500, "Failed to retrieve tasks");
        }
    }

    [HttpPost("tasks")]
    public async Task<ActionResult> CreateTask([FromBody] CreateTaskRequest request)
    {
        try
        {
            var command = new CreateAgentTaskCommand(
                request.Title,
                request.Description,
                request.Priority,
                request.RequiredCapabilities);

            var taskId = await _mediator.Send(command);
            return CreatedAtAction(nameof(GetTask), new { id = taskId }, new { Id = taskId });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error creating task");
            return StatusCode(500, "Failed to create task");
        }
    }

    [HttpGet("tasks/{id}")]
    public async Task<ActionResult> GetTask(Guid id)
    {
        try
        {
            var tasks = await _mediator.Send(new GetTasksQuery());
            var task = tasks.FirstOrDefault(t => t.Id == id);
            
            if (task == null)
                return NotFound();
                
            return Ok(task);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error retrieving task {TaskId}", id);
            return StatusCode(500, "Failed to retrieve task");
        }
    }

    [HttpPost("tasks/{id}/assign")]
    public async Task<ActionResult> AssignTask(Guid id, [FromBody] AssignTaskRequest request)
    {
        try
        {
            var command = new AssignTaskToAgentCommand(id, request.AgentId);
            await _mediator.Send(command);
            return Ok();
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error assigning task {TaskId} to agent {AgentId}", id, request.AgentId);
            return StatusCode(500, "Failed to assign task");
        }
    }

    [HttpGet("pending-tasks")]
    public async Task<ActionResult> GetPendingTasks()
    {
        try
        {
            var tasks = await _agentOrchestrator.GetPendingTasksAsync();
            return Ok(tasks);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error retrieving pending tasks");
            return StatusCode(500, "Failed to retrieve pending tasks");
        }
    }

    [HttpPost("process-pending-tasks")]
    public async Task<ActionResult> ProcessPendingTasks()
    {
        try
        {
            await _agentOrchestrator.ProcessPendingTasksAsync();
            return Ok(new { Message = "Pending tasks processing initiated" });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error processing pending tasks");
            return StatusCode(500, "Failed to process pending tasks");
        }
    }
}

public class CreateAgentRequest
{
    public string Name { get; set; } = string.Empty;
    public string Type { get; set; } = string.Empty;
    public Dictionary<string, object> Configuration { get; set; } = new();
    public string? Description { get; set; }
}

public class CreateTaskRequest
{
    public string Title { get; set; } = string.Empty;
    public string? Description { get; set; }
    public string Priority { get; set; } = "Medium";
    public List<string> RequiredCapabilities { get; set; } = new();
}

public class AssignTaskRequest
{
    public Guid AgentId { get; set; }
}