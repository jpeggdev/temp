using HeyDav.Application.Email;

namespace HeyDav.Infrastructure.Email;

public interface IEmailService
{
    Task<bool> SendEmailAsync(string to, string subject, string body);
    Task<List<EmailCommand>> CheckForNewEmailsAsync();
    Task StartEmailMonitoringAsync();
    Task StopEmailMonitoringAsync();
    bool IsMonitoring { get; }
}

public class EmailConfiguration
{
    public string SmtpServer { get; set; } = string.Empty;
    public int SmtpPort { get; set; } = 587;
    public string Username { get; set; } = string.Empty;
    public string Password { get; set; } = string.Empty;
    public bool UseSsl { get; set; } = true;
    
    public string ImapServer { get; set; } = string.Empty;
    public int ImapPort { get; set; } = 993;
    public string InboxFolder { get; set; } = "INBOX";
    public int CheckIntervalMinutes { get; set; } = 5;
}