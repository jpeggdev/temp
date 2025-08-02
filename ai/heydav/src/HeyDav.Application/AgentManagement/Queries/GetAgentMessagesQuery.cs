using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.AgentManagement.Communication;

namespace HeyDav.Application.AgentManagement.Queries;

public record GetAgentMessagesQuery(
    Guid AgentId,
    bool MarkAsRead = true,
    bool UnreadOnly = false) : IQuery<IEnumerable<AgentMessage>>;

public class GetAgentMessagesQueryHandler(IAgentCommunicationHub communicationHub) 
    : IQueryHandler<GetAgentMessagesQuery, IEnumerable<AgentMessage>>
{
    private readonly IAgentCommunicationHub _communicationHub = communicationHub ?? throw new ArgumentNullException(nameof(communicationHub));

    public async Task<IEnumerable<AgentMessage>> Handle(GetAgentMessagesQuery request, CancellationToken cancellationToken)
    {
        if (request.UnreadOnly)
        {
            return await _communicationHub.GetUnreadMessagesAsync(request.AgentId, cancellationToken);
        }

        return await _communicationHub.GetMessagesAsync(request.AgentId, request.MarkAsRead, cancellationToken);
    }
}