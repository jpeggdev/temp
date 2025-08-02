using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.AgentManagement.Communication;

namespace HeyDav.Application.AgentManagement.Commands;

public record SendAgentMessageCommand(
    Guid FromAgentId,
    Guid ToAgentId,
    string Content,
    MessageType Type = MessageType.Text,
    MessagePriority Priority = MessagePriority.Medium,
    Dictionary<string, object>? Attachments = null) : ICommand<bool>;

public class SendAgentMessageCommandHandler(IAgentCommunicationHub communicationHub) 
    : ICommandHandler<SendAgentMessageCommand, bool>
{
    private readonly IAgentCommunicationHub _communicationHub = communicationHub ?? throw new ArgumentNullException(nameof(communicationHub));

    public async Task<bool> Handle(SendAgentMessageCommand request, CancellationToken cancellationToken)
    {
        var message = new AgentMessage(
            Guid.NewGuid(),
            request.FromAgentId,
            request.ToAgentId,
            request.Content,
            request.Type,
            request.Priority,
            request.Attachments
        );

        return await _communicationHub.SendMessageAsync(request.FromAgentId, request.ToAgentId, message, cancellationToken);
    }
}