using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.AgentManagement.Workspace;

namespace HeyDav.Application.AgentManagement.Queries;

public record SearchWorkspacesQuery(
    string Query,
    Guid? RequestingAgentId = null) : IQuery<IEnumerable<WorkspaceSearchResult>>;

public class SearchWorkspacesQueryHandler(ISharedWorkspaceManager workspaceManager) 
    : IQueryHandler<SearchWorkspacesQuery, IEnumerable<WorkspaceSearchResult>>
{
    private readonly ISharedWorkspaceManager _workspaceManager = workspaceManager ?? throw new ArgumentNullException(nameof(workspaceManager));

    public async Task<IEnumerable<WorkspaceSearchResult>> Handle(SearchWorkspacesQuery request, CancellationToken cancellationToken)
    {
        return await _workspaceManager.SearchWorkspacesAsync(request.Query, request.RequestingAgentId, cancellationToken);
    }
}