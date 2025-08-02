using Microsoft.Extensions.DependencyInjection;
using HeyDav.Application.Common.Interfaces;

namespace HeyDav.Application.Common.Services;

public class Mediator(IServiceProvider serviceProvider) : IMediator
{
    private readonly IServiceProvider _serviceProvider = serviceProvider ?? throw new ArgumentNullException(nameof(serviceProvider));

    public async Task<TResponse> Send<TResponse>(ICommand<TResponse> command, CancellationToken cancellationToken = default)
    {
        if (command == null)
            throw new ArgumentNullException(nameof(command));

        var commandType = command.GetType();
        var handlerType = typeof(ICommandHandler<,>).MakeGenericType(commandType, typeof(TResponse));
        
        var handler = _serviceProvider.GetRequiredService(handlerType);
        if (handler == null)
            throw new InvalidOperationException($"No handler found for command type {commandType.Name}");

        var method = handlerType.GetMethod("Handle") 
            ?? throw new InvalidOperationException($"Handle method not found on handler type {handlerType.Name}");
            
        var task = method.Invoke(handler, new object[] { command, cancellationToken }) as Task;
        if (task == null)
            throw new InvalidOperationException($"Handler returned null task for command type {commandType.Name}");

        await task;

        var resultProperty = task.GetType().GetProperty("Result");
        if (resultProperty == null)
            throw new InvalidOperationException($"Result property not found on task for command type {commandType.Name}");

        return (TResponse)resultProperty.GetValue(task)!;
    }

    public async Task<TResponse> Send<TResponse>(IQuery<TResponse> query, CancellationToken cancellationToken = default)
    {
        if (query == null)
            throw new ArgumentNullException(nameof(query));

        var queryType = query.GetType();
        var handlerType = typeof(IQueryHandler<,>).MakeGenericType(queryType, typeof(TResponse));
        
        var handler = _serviceProvider.GetRequiredService(handlerType);
        if (handler == null)
            throw new InvalidOperationException($"No handler found for query type {queryType.Name}");

        var method = handlerType.GetMethod("Handle") 
            ?? throw new InvalidOperationException($"Handle method not found on handler type {handlerType.Name}");
            
        var task = method.Invoke(handler, new object[] { query, cancellationToken }) as Task;
        if (task == null)
            throw new InvalidOperationException($"Handler returned null task for query type {queryType.Name}");

        await task;

        var resultProperty = task.GetType().GetProperty("Result");
        if (resultProperty == null)
            throw new InvalidOperationException($"Result property not found on task for query type {queryType.Name}");

        return (TResponse)resultProperty.GetValue(task)!;
    }
}