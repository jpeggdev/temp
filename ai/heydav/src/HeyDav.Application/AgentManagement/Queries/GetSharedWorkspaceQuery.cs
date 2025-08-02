using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.AgentManagement.Workspace;

namespace HeyDav.Application.AgentManagement.Queries;

public record GetSharedWorkspaceQuery(Guid WorkspaceId) : IQuery<SharedWorkspace?>;

public class GetSharedWorkspaceQueryHandler(ISharedWorkspaceManager workspaceManager) 
    : IQueryHandler<GetSharedWorkspaceQuery, SharedWorkspace?>
{
    private readonly ISharedWorkspaceManager _workspaceManager = workspaceManager ?? throw new ArgumentNullException(nameof(workspaceManager));

    public async Task<SharedWorkspace?> Handle(GetSharedWorkspaceQuery request, CancellationToken cancellationToken)
    {
        return await _workspaceManager.GetWorkspaceAsync(request.WorkspaceId, cancellationToken);
    }
}