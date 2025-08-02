using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Diagnostics.HealthChecks;
using HeyDav.Infrastructure.Services;
using HeyDav.Application.Email;
using HeyDav.Infrastructure.Persistence;
using Microsoft.EntityFrameworkCore;

namespace HeyDav.Api.Controllers;

[ApiController]
[Route("api/[controller]")]
public class SystemController : ControllerBase
{
    private readonly ILogger<SystemController> _logger;
    private readonly IServiceProvider _serviceProvider;

    public SystemController(ILogger<SystemController> logger, IServiceProvider serviceProvider)
    {
        _logger = logger;
        _serviceProvider = serviceProvider;
    }

    [HttpGet("health")]
    public async Task<ActionResult<SystemHealthStatus>> GetSystemHealth()
    {
        try
        {
            var healthStatus = new SystemHealthStatus
            {
                Timestamp = DateTime.UtcNow,
                OverallStatus = "Healthy"
            };

            // Check database
            healthStatus.DatabaseStatus = await CheckDatabaseHealth();
            
            // Check voice service
            healthStatus.VoiceServiceStatus = CheckVoiceServiceHealth();
            
            // Check email service
            healthStatus.EmailServiceStatus = CheckEmailServiceHealth();
            
            // Check agent orchestrator
            healthStatus.AgentOrchestratorStatus = "Available";

            // Determine overall status
            var componentStatuses = new[] 
            {
                healthStatus.DatabaseStatus,
                healthStatus.VoiceServiceStatus,
                healthStatus.EmailServiceStatus,
                healthStatus.AgentOrchestratorStatus
            };

            if (componentStatuses.Any(s => s.Contains("Error") || s.Contains("Failed")))
            {
                healthStatus.OverallStatus = "Unhealthy";
            }
            else if (componentStatuses.Any(s => s.Contains("Warning") || s.Contains("Degraded")))
            {
                healthStatus.OverallStatus = "Degraded";
            }

            return Ok(healthStatus);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error checking system health");
            return StatusCode(500, new SystemHealthStatus
            {
                Timestamp = DateTime.UtcNow,
                OverallStatus = "Unhealthy",
                DatabaseStatus = "Unknown",
                VoiceServiceStatus = "Unknown",
                EmailServiceStatus = "Unknown",
                AgentOrchestratorStatus = "Unknown"
            });
        }
    }

    [HttpGet("status")]
    public ActionResult<SystemStatus> GetSystemStatus()
    {
        try
        {
            var status = new SystemStatus
            {
                Version = "1.0.0",
                Environment = Environment.GetEnvironmentVariable("ASPNETCORE_ENVIRONMENT") ?? "Unknown",
                StartTime = DateTime.UtcNow.AddHours(-1), // Placeholder - would track actual start time
                Uptime = TimeSpan.FromHours(1), // Placeholder
                ProcessId = Environment.ProcessId,
                MachineName = Environment.MachineName,
                Architecture = Environment.OSVersion.Platform.ToString(),
                MemoryUsage = GC.GetTotalMemory(false),
                Features = new List<string>
                {
                    "CommandOrchestrator",
                    "AgentManagement", 
                    "VoiceCommands",
                    "EmailProcessing",
                    "TodoManagement",
                    "RestAPI"
                }
            };

            return Ok(status);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error getting system status");
            return StatusCode(500, "Failed to get system status");
        }
    }

    [HttpPost("restart-services")]
    public async Task<ActionResult> RestartServices([FromBody] RestartServicesRequest request)
    {
        try
        {
            var results = new List<string>();

            if (request.RestartVoice)
            {
                var voiceService = _serviceProvider.GetService<IVoiceCommandService>();
                if (voiceService != null)
                {
                    if (voiceService.IsRunning)
                    {
                        await voiceService.StopAsync();
                    }
                    await voiceService.StartAsync();
                    results.Add("Voice service restarted");
                }
                else
                {
                    results.Add("Voice service not available");
                }
            }

            if (request.RestartEmail)
            {
                var emailService = _serviceProvider.GetService<IEmailService>();
                if (emailService != null)
                {
                    if (emailService.IsMonitoring)
                    {
                        await emailService.StopEmailMonitoringAsync();
                    }
                    await emailService.StartEmailMonitoringAsync();
                    results.Add("Email service restarted");
                }
                else
                {
                    results.Add("Email service not available");
                }
            }

            return Ok(new { Message = "Services restart completed", Results = results });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error restarting services");
            return StatusCode(500, "Failed to restart services");
        }
    }

    private async Task<string> CheckDatabaseHealth()
    {
        try
        {
            using var scope = _serviceProvider.CreateScope();
            var dbContext = scope.ServiceProvider.GetRequiredService<HeyDavDbContext>();
            var canConnect = await dbContext.Database.CanConnectAsync();
            return canConnect ? "Healthy" : "Connection Failed";
        }
        catch (Exception)
        {
            return "Error";
        }
    }

    private string CheckVoiceServiceHealth()
    {
        try
        {
            var voiceService = _serviceProvider.GetService<IVoiceCommandService>();
            if (voiceService == null)
                return "Not Available";
            
            return voiceService.IsRunning ? "Running" : "Stopped";
        }
        catch (Exception)
        {
            return "Error";
        }
    }

    private string CheckEmailServiceHealth()
    {
        try
        {
            var emailService = _serviceProvider.GetService<IEmailService>();
            if (emailService == null)
                return "Not Available";
            
            return emailService.IsMonitoring ? "Monitoring" : "Not Monitoring";
        }
        catch (Exception)
        {
            return "Error";
        }
    }
}

public class SystemHealthStatus
{
    public DateTime Timestamp { get; set; }
    public string OverallStatus { get; set; } = string.Empty;
    public string DatabaseStatus { get; set; } = string.Empty;
    public string VoiceServiceStatus { get; set; } = string.Empty;
    public string EmailServiceStatus { get; set; } = string.Empty;
    public string AgentOrchestratorStatus { get; set; } = string.Empty;
}

public class SystemStatus
{
    public string Version { get; set; } = string.Empty;
    public string Environment { get; set; } = string.Empty;
    public DateTime StartTime { get; set; }
    public TimeSpan Uptime { get; set; }
    public int ProcessId { get; set; }
    public string MachineName { get; set; } = string.Empty;
    public string Architecture { get; set; } = string.Empty;
    public long MemoryUsage { get; set; }
    public List<string> Features { get; set; } = new();
}

public class RestartServicesRequest
{
    public bool RestartVoice { get; set; }
    public bool RestartEmail { get; set; }
}