using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.AgentManagement.Workspace;

namespace HeyDav.Application.AgentManagement.Commands;

public record CreateSharedWorkspaceCommand(
    string Name,
    string Description,
    WorkspaceType Type,
    Guid OwnerId,
    WorkspaceVisibility Visibility = WorkspaceVisibility.Private,
    Dictionary<string, object>? Settings = null,
    IEnumerable<string>? Tags = null) : ICommand<Guid>;

public class CreateSharedWorkspaceCommandHandler(ISharedWorkspaceManager workspaceManager) 
    : ICommandHandler<CreateSharedWorkspaceCommand, Guid>
{
    private readonly ISharedWorkspaceManager _workspaceManager = workspaceManager ?? throw new ArgumentNullException(nameof(workspaceManager));

    public async Task<Guid> Handle(CreateSharedWorkspaceCommand request, CancellationToken cancellationToken)
    {
        var definition = new WorkspaceDefinition(
            request.Name,
            request.Description,
            request.Type,
            request.OwnerId,
            request.Visibility,
            request.Settings,
            request.Tags
        );

        return await _workspaceManager.CreateWorkspaceAsync(definition, cancellationToken);
    }
}