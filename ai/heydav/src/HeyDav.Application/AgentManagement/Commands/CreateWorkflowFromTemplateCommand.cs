using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.AgentManagement.Services;

namespace HeyDav.Application.AgentManagement.Commands;

public record CreateWorkflowFromTemplateCommand(
    string TemplateName,
    Dictionary<string, object>? Parameters = null) : ICommand<Guid>;

public class CreateWorkflowFromTemplateCommandHandler(IAgentWorkflowEngine workflowEngine) 
    : ICommandHandler<CreateWorkflowFromTemplateCommand, Guid>
{
    private readonly IAgentWorkflowEngine _workflowEngine = workflowEngine ?? throw new ArgumentNullException(nameof(workflowEngine));

    public async Task<Guid> Handle(CreateWorkflowFromTemplateCommand request, CancellationToken cancellationToken)
    {
        var template = await _workflowEngine.GetTemplateAsync(request.TemplateName, cancellationToken);
        if (template == null)
        {
            throw new ArgumentException($"Workflow template '{request.TemplateName}' not found");
        }

        // Apply parameters to template if provided
        var workflowDefinition = template;
        if (request.Parameters != null)
        {
            // Merge parameters with default data
            var mergedData = new Dictionary<string, object>(template.DefaultData ?? new Dictionary<string, object>());
            foreach (var param in request.Parameters)
            {
                mergedData[param.Key] = param.Value;
            }
            workflowDefinition = template with { DefaultData = mergedData };
        }

        return await _workflowEngine.CreateWorkflowAsync(workflowDefinition, cancellationToken);
    }
}