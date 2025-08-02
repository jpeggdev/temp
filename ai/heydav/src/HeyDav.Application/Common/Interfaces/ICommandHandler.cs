namespace HeyDav.Application.Common.Interfaces;

public interface ICommandHandler<TCommand, TResponse> where TCommand : ICommand<TResponse>
{
    Task<TResponse> Handle(TCommand command, CancellationToken cancellationToken = default);
}

public interface ICommandHandler<TCommand> : ICommandHandler<TCommand, Unit> where TCommand : ICommand
{
}