using Microsoft.Extensions.Hosting;
using Microsoft.Extensions.Logging;
using Microsoft.Extensions.DependencyInjection;
using HeyDav.Infrastructure.Services;

namespace HeyDav.Infrastructure.BackgroundServices;

public class AgentTaskProcessingService : BackgroundService
{
    private readonly IServiceProvider _serviceProvider;
    private readonly ILogger<AgentTaskProcessingService> _logger;
    private readonly TimeSpan _processingInterval = TimeSpan.FromSeconds(30); // Process tasks every 30 seconds

    public AgentTaskProcessingService(
        IServiceProvider serviceProvider,
        ILogger<AgentTaskProcessingService> logger)
    {
        _serviceProvider = serviceProvider;
        _logger = logger;
    }

    protected override async Task ExecuteAsync(CancellationToken stoppingToken)
    {
        _logger.LogInformation("Agent task processing service started");

        while (!stoppingToken.IsCancellationRequested)
        {
            try
            {
                using var scope = _serviceProvider.CreateScope();
                var agentOrchestrator = scope.ServiceProvider.GetRequiredService<IAgentOrchestrator>();

                _logger.LogDebug("Processing pending agent tasks");
                await agentOrchestrator.ProcessPendingTasksAsync(stoppingToken);

                _logger.LogDebug("Monitoring agent health");
                await agentOrchestrator.MonitorAgentHealthAsync(stoppingToken);

                await Task.Delay(_processingInterval, stoppingToken);
            }
            catch (OperationCanceledException)
            {
                // Expected when cancellation is requested
                break;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error occurred during agent task processing");
                
                // Wait a bit longer on error to avoid tight error loops
                try
                {
                    await Task.Delay(TimeSpan.FromMinutes(1), stoppingToken);
                }
                catch (OperationCanceledException)
                {
                    break;
                }
            }
        }

        _logger.LogInformation("Agent task processing service stopped");
    }

    public override async Task StopAsync(CancellationToken cancellationToken)
    {
        _logger.LogInformation("Agent task processing service stopping");
        await base.StopAsync(cancellationToken);
    }
}