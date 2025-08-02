using HeyDav.Application.Common.Interfaces;
using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Interfaces;
using HeyDav.Domain.AgentManagement.ValueObjects;

namespace HeyDav.Application.AgentManagement.Commands;

public record CreateMcpServerCommand(
    string Name,
    string Protocol,
    string Host,
    int Port,
    string? Path = null,
    string? Description = null,
    Dictionary<string, string>? Headers = null,
    bool RequiresAuthentication = false) : ICommand<Guid>;

public class CreateMcpServerCommandHandler(IMapServerRepository repository)
    : ICommandHandler<CreateMcpServerCommand, Guid>
{
    private readonly IMapServerRepository _repository = repository ?? throw new ArgumentNullException(nameof(repository));

    public async Task<Guid> Handle(CreateMcpServerCommand request, CancellationToken cancellationToken)
    {
        var endpoint = McpServerEndpoint.Create(
            request.Name,
            request.Protocol,
            request.Host,
            request.Port,
            request.Path,
            request.Headers,
            request.RequiresAuthentication);

        var server = McpServer.Create(
            request.Name,
            endpoint,
            request.Description);

        await _repository.AddAsync(server, cancellationToken);
        
        return server.Id;
    }
}