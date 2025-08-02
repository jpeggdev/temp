using Microsoft.Extensions.Hosting;
using Microsoft.Extensions.Logging;
using Microsoft.Extensions.DependencyInjection;
using HeyDav.Infrastructure.Services;

namespace HeyDav.Infrastructure.BackgroundServices;

public class SystemHealthMonitoringService : BackgroundService
{
    private readonly IServiceProvider _serviceProvider;
    private readonly ILogger<SystemHealthMonitoringService> _logger;
    private readonly TimeSpan _healthCheckInterval = TimeSpan.FromMinutes(5); // Health check every 5 minutes

    public SystemHealthMonitoringService(
        IServiceProvider serviceProvider,
        ILogger<SystemHealthMonitoringService> logger)
    {
        _serviceProvider = serviceProvider;
        _logger = logger;
    }

    protected override async Task ExecuteAsync(CancellationToken stoppingToken)
    {
        _logger.LogInformation("System health monitoring service started");

        while (!stoppingToken.IsCancellationRequested)
        {
            try
            {
                using var scope = _serviceProvider.CreateScope();
                
                await CheckDatabaseHealth(scope, stoppingToken);
                await CheckAgentHealth(scope, stoppingToken);
                await CheckVoiceServiceHealth(scope, stoppingToken);
                await CheckEmailServiceHealth(scope, stoppingToken);

                await Task.Delay(_healthCheckInterval, stoppingToken);
            }
            catch (OperationCanceledException)
            {
                // Expected when cancellation is requested
                break;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error occurred during system health monitoring");
                
                // Wait a bit on error
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

        _logger.LogInformation("System health monitoring service stopped");
    }

    private async Task CheckDatabaseHealth(IServiceScope scope, CancellationToken cancellationToken)
    {
        try
        {
            var dbContext = scope.ServiceProvider.GetRequiredService<Persistence.HeyDavDbContext>();
            await dbContext.Database.CanConnectAsync(cancellationToken);
            _logger.LogDebug("Database health check: OK");
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "Database health check failed");
        }
    }

    private async Task CheckAgentHealth(IServiceScope scope, CancellationToken cancellationToken)
    {
        try
        {
            var agentOrchestrator = scope.ServiceProvider.GetRequiredService<IAgentOrchestrator>();
            await agentOrchestrator.MonitorAgentHealthAsync(cancellationToken);
            _logger.LogDebug("Agent health check: OK");
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "Agent health check failed");
        }
    }

    private async Task CheckVoiceServiceHealth(IServiceScope scope, CancellationToken cancellationToken)
    {
        try
        {
            var voiceService = scope.ServiceProvider.GetService<IVoiceCommandService>();
            if (voiceService != null)
            {
                var isHealthy = voiceService.IsRunning;
                _logger.LogDebug("Voice service health check: {Status}", isHealthy ? "OK" : "Not Running");
            }
            await Task.CompletedTask;
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "Voice service health check failed");
        }
    }

    private async Task CheckEmailServiceHealth(IServiceScope scope, CancellationToken cancellationToken)
    {
        try
        {
            var emailService = scope.ServiceProvider.GetService<HeyDav.Infrastructure.Email.IEmailService>();
            if (emailService != null)
            {
                var isHealthy = emailService.IsMonitoring;
                _logger.LogDebug("Email service health check: {Status}", isHealthy ? "OK" : "Not Monitoring");
            }
            await Task.CompletedTask;
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "Email service health check failed");
        }
    }

    public override async Task StopAsync(CancellationToken cancellationToken)
    {
        _logger.LogInformation("System health monitoring service stopping");
        await base.StopAsync(cancellationToken);
    }
}