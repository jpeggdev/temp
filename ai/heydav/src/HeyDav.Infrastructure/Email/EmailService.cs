using HeyDav.Application.Email;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;
using System.Net.Mail;
using System.Net;
using System.Text.RegularExpressions;

namespace HeyDav.Infrastructure.Email;

public class EmailService : IEmailService, IDisposable
{
    private readonly ILogger<EmailService> _logger;
    private readonly EmailConfiguration _config;
    private readonly Timer? _monitoringTimer;
    private bool _isMonitoring;
    private readonly HashSet<string> _processedMessageIds = new();

    public EmailService(IConfiguration configuration, ILogger<EmailService> logger)
    {
        _logger = logger;
        _config = new EmailConfiguration();
        configuration.GetSection("Email").Bind(_config);
    }

    public bool IsMonitoring => _isMonitoring;

    public async Task<bool> SendEmailAsync(string to, string subject, string body)
    {
        try
        {
            using var client = new SmtpClient(_config.SmtpServer, _config.SmtpPort)
            {
                Credentials = new NetworkCredential(_config.Username, _config.Password),
                EnableSsl = _config.UseSsl
            };

            using var message = new MailMessage(_config.Username, to, subject, body);
            await client.SendMailAsync(message);
            
            _logger.LogInformation("Email sent successfully to {To}", to);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to send email to {To}", to);
            return false;
        }
    }

    public async Task<List<EmailCommand>> CheckForNewEmailsAsync()
    {
        var emails = new List<EmailCommand>();
        
        try
        {
            // For now, this is a placeholder implementation
            // In a real implementation, you would use IMAP libraries like MailKit
            // to connect to email servers and fetch new messages
            
            _logger.LogInformation("Checking for new emails...");
            
            // Simulated email check - replace with actual IMAP implementation
            await Task.Delay(100);
            
            return emails;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error checking for new emails");
            return emails;
        }
    }

    public async Task StartEmailMonitoringAsync()
    {
        if (_isMonitoring)
        {
            _logger.LogWarning("Email monitoring is already running");
            return;
        }

        _isMonitoring = true;
        _logger.LogInformation("Started email monitoring with {Interval} minute intervals", _config.CheckIntervalMinutes);
        
        // Start periodic checking
        var timer = new Timer(async _ => await CheckEmailsCallback(), null, 
            TimeSpan.Zero, TimeSpan.FromMinutes(_config.CheckIntervalMinutes));
        
        await Task.CompletedTask;
    }

    public async Task StopEmailMonitoringAsync()
    {
        if (!_isMonitoring)
        {
            _logger.LogWarning("Email monitoring is not running");
            return;
        }

        _isMonitoring = false;
        _monitoringTimer?.Dispose();
        _logger.LogInformation("Stopped email monitoring");
        
        await Task.CompletedTask;
    }

    private async Task CheckEmailsCallback()
    {
        try
        {
            var newEmails = await CheckForNewEmailsAsync();
            if (newEmails.Any())
            {
                _logger.LogInformation("Found {Count} new emails to process", newEmails.Count);
                // Here you would typically raise an event or call a processor
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error during periodic email check");
        }
    }

    public void Dispose()
    {
        _monitoringTimer?.Dispose();
        GC.SuppressFinalize(this);
    }
}

// Background service to handle email processing
public class EmailMonitoringService : Microsoft.Extensions.Hosting.BackgroundService
{
    private readonly IEmailService _emailService;
    private readonly IEmailCommandProcessor _processor;
    private readonly ILogger<EmailMonitoringService> _logger;

    public EmailMonitoringService(
        IEmailService emailService, 
        IEmailCommandProcessor processor,
        ILogger<EmailMonitoringService> logger)
    {
        _emailService = emailService;
        _processor = processor;
        _logger = logger;
    }

    protected override async Task ExecuteAsync(CancellationToken stoppingToken)
    {
        _logger.LogInformation("Email monitoring background service started");
        
        await _emailService.StartEmailMonitoringAsync();

        while (!stoppingToken.IsCancellationRequested)
        {
            try
            {
                var emails = await _emailService.CheckForNewEmailsAsync();
                
                foreach (var email in emails)
                {
                    var result = await _processor.ProcessEmailAsync(email);
                    
                    if (result.Success)
                    {
                        var response = await _processor.GenerateEmailResponseAsync(result);
                        await _emailService.SendEmailAsync(email.From, $"Re: {email.Subject}", response);
                        _logger.LogInformation("Processed and responded to email from {From}", email.From);
                    }
                    else
                    {
                        _logger.LogWarning("Failed to process email from {From}: {Message}", email.From, result.Message);
                    }
                }

                await Task.Delay(TimeSpan.FromMinutes(5), stoppingToken);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error in email monitoring loop");
                await Task.Delay(TimeSpan.FromMinutes(1), stoppingToken);
            }
        }
    }

    public override async Task StopAsync(CancellationToken cancellationToken)
    {
        _logger.LogInformation("Email monitoring background service stopping");
        await _emailService.StopEmailMonitoringAsync();
        await base.StopAsync(cancellationToken);
    }
}