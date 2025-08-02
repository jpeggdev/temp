using Microsoft.EntityFrameworkCore;
using HeyDav.Domain.ToolIntegrations.Entities;
using HeyDav.Domain.ToolIntegrations.Enums;
using HeyDav.Domain.ToolIntegrations.Interfaces;

namespace HeyDav.Infrastructure.Persistence.Repositories;

public class ToolConnectionRepository : Repository<ToolConnection>, IToolConnectionRepository
{
    public ToolConnectionRepository(HeyDavDbContext context) : base(context)
    {
    }

    public async Task<ToolConnection?> GetByNameAsync(string name, CancellationToken cancellationToken = default)
    {
        return await Context.ToolConnections
            .Include(tc => tc.Capabilities)
            .Include(tc => tc.WebhookEndpoints)
            .Include(tc => tc.SyncConfigurations)
            .FirstOrDefaultAsync(tc => tc.Name == name, cancellationToken);
    }

    public async Task<IEnumerable<ToolConnection>> GetByTypeAsync(ToolType toolType, CancellationToken cancellationToken = default)
    {
        return await Context.ToolConnections
            .Include(tc => tc.Capabilities)
            .Where(tc => tc.ToolType == toolType)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<ToolConnection>> GetByStatusAsync(ConnectionStatus status, CancellationToken cancellationToken = default)
    {
        return await Context.ToolConnections
            .Where(tc => tc.Status == status)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<ToolConnection>> GetHealthyConnectionsAsync(CancellationToken cancellationToken = default)
    {
        return await Context.ToolConnections
            .Where(tc => tc.Status == ConnectionStatus.Connected && tc.IsEnabled && tc.ConsecutiveFailures < 3)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<ToolConnection>> GetConnectionsNeedingHealthCheckAsync(CancellationToken cancellationToken = default)
    {
        var tenMinutesAgo = DateTime.UtcNow.AddMinutes(-10);
        return await Context.ToolConnections
            .Where(tc => tc.IsEnabled && (tc.LastHealthCheck == null || tc.LastHealthCheck < tenMinutesAgo))
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<ToolConnection>> GetEnabledConnectionsAsync(CancellationToken cancellationToken = default)
    {
        return await Context.ToolConnections
            .Where(tc => tc.IsEnabled)
            .ToListAsync(cancellationToken);
    }
}

public class ToolCapabilityRepository : Repository<ToolCapability>, IToolCapabilityRepository
{
    public ToolCapabilityRepository(HeyDavDbContext context) : base(context)
    {
    }

    public async Task<IEnumerable<ToolCapability>> GetByToolConnectionIdAsync(Guid toolConnectionId, CancellationToken cancellationToken = default)
    {
        return await Context.ToolCapabilities
            .Where(tc => tc.ToolConnectionId == toolConnectionId)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<ToolCapability>> GetByTypeAsync(CapabilityType type, CancellationToken cancellationToken = default)
    {
        return await Context.ToolCapabilities
            .Where(tc => tc.Type == type)
            .ToListAsync(cancellationToken);
    }

    public async Task<ToolCapability?> GetByNameAndConnectionAsync(string name, Guid toolConnectionId, CancellationToken cancellationToken = default)
    {
        return await Context.ToolCapabilities
            .FirstOrDefaultAsync(tc => tc.Name == name && tc.ToolConnectionId == toolConnectionId, cancellationToken);
    }

    public async Task<IEnumerable<ToolCapability>> GetEnabledCapabilitiesAsync(Guid toolConnectionId, CancellationToken cancellationToken = default)
    {
        return await Context.ToolCapabilities
            .Where(tc => tc.ToolConnectionId == toolConnectionId && tc.IsEnabled)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<ToolCapability>> GetCapabilitiesWithUsageAsync(CancellationToken cancellationToken = default)
    {
        return await Context.ToolCapabilities
            .Where(tc => tc.TotalUsageCount > 0)
            .OrderByDescending(tc => tc.LastUsed)
            .ToListAsync(cancellationToken);
    }
}

public class WebhookEndpointRepository : Repository<WebhookEndpoint>, IWebhookEndpointRepository
{
    public WebhookEndpointRepository(HeyDavDbContext context) : base(context)
    {
    }

    public async Task<IEnumerable<WebhookEndpoint>> GetByToolConnectionIdAsync(Guid toolConnectionId, CancellationToken cancellationToken = default)
    {
        return await Context.WebhookEndpoints
            .Include(we => we.Events.OrderByDescending(e => e.ReceivedAt).Take(10))
            .Where(we => we.ToolConnectionId == toolConnectionId)
            .ToListAsync(cancellationToken);
    }

    public async Task<WebhookEndpoint?> GetByEndpointUrlAsync(string endpointUrl, CancellationToken cancellationToken = default)
    {
        return await Context.WebhookEndpoints
            .FirstOrDefaultAsync(we => we.EndpointUrl == endpointUrl, cancellationToken);
    }

    public async Task<IEnumerable<WebhookEndpoint>> GetByStatusAsync(WebhookStatus status, CancellationToken cancellationToken = default)
    {
        return await Context.WebhookEndpoints
            .Where(we => we.Status == status)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<WebhookEndpoint>> GetActiveEndpointsAsync(CancellationToken cancellationToken = default)
    {
        return await Context.WebhookEndpoints
            .Where(we => we.IsEnabled && we.Status == WebhookStatus.Active)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<WebhookEndpoint>> GetEndpointsByEventTypeAsync(string eventType, CancellationToken cancellationToken = default)
    {
        return await Context.WebhookEndpoints
            .Where(we => we.IsEnabled && we.EnabledEvents.Contains(eventType))
            .ToListAsync(cancellationToken);
    }
}

public class WebhookEventRepository : Repository<WebhookEvent>, IWebhookEventRepository
{
    public WebhookEventRepository(HeyDavDbContext context) : base(context)
    {
    }

    public async Task<IEnumerable<WebhookEvent>> GetByWebhookEndpointIdAsync(Guid webhookEndpointId, CancellationToken cancellationToken = default)
    {
        return await Context.WebhookEvents
            .Where(we => we.WebhookEndpointId == webhookEndpointId)
            .OrderByDescending(we => we.ReceivedAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<WebhookEvent>> GetByStatusAsync(WebhookEventStatus status, CancellationToken cancellationToken = default)
    {
        return await Context.WebhookEvents
            .Where(we => we.Status == status)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<WebhookEvent>> GetPendingEventsAsync(CancellationToken cancellationToken = default)
    {
        return await Context.WebhookEvents
            .Where(we => we.Status == WebhookEventStatus.Pending)
            .OrderBy(we => we.ReceivedAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<WebhookEvent>> GetFailedEventsForRetryAsync(CancellationToken cancellationToken = default)
    {
        var now = DateTime.UtcNow;
        return await Context.WebhookEvents
            .Where(we => we.Status == WebhookEventStatus.Failed && 
                        we.NextRetryAt.HasValue && 
                        we.NextRetryAt.Value <= now &&
                        we.RetryCount < 3)
            .OrderBy(we => we.NextRetryAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<WebhookEvent>> GetEventsByTypeAsync(string eventType, CancellationToken cancellationToken = default)
    {
        return await Context.WebhookEvents
            .Where(we => we.EventType == eventType)
            .OrderByDescending(we => we.ReceivedAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<WebhookEvent>> GetRecentEventsAsync(TimeSpan timeSpan, CancellationToken cancellationToken = default)
    {
        var cutoffTime = DateTime.UtcNow - timeSpan;
        return await Context.WebhookEvents
            .Where(we => we.ReceivedAt >= cutoffTime)
            .OrderByDescending(we => we.ReceivedAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<WebhookEvent?> GetByEventIdAsync(string eventId, CancellationToken cancellationToken = default)
    {
        return await Context.WebhookEvents
            .FirstOrDefaultAsync(we => we.EventId == eventId, cancellationToken);
    }

    public async Task<int> GetPendingEventCountAsync(Guid webhookEndpointId, CancellationToken cancellationToken = default)
    {
        return await Context.WebhookEvents
            .CountAsync(we => we.WebhookEndpointId == webhookEndpointId && we.Status == WebhookEventStatus.Pending, cancellationToken);
    }

    public async Task DeleteOldEventsAsync(TimeSpan maxAge, CancellationToken cancellationToken = default)
    {
        var cutoffTime = DateTime.UtcNow - maxAge;
        var oldEvents = await Context.WebhookEvents
            .Where(we => we.ReceivedAt < cutoffTime && we.Status == WebhookEventStatus.Processed)
            .ToListAsync(cancellationToken);

        Context.WebhookEvents.RemoveRange(oldEvents);
        await Context.SaveChangesAsync(cancellationToken);
    }
}

public class ToolSyncConfigurationRepository : Repository<ToolSyncConfiguration>, IToolSyncConfigurationRepository
{
    public ToolSyncConfigurationRepository(HeyDavDbContext context) : base(context)
    {
    }

    public async Task<IEnumerable<ToolSyncConfiguration>> GetByToolConnectionIdAsync(Guid toolConnectionId, CancellationToken cancellationToken = default)
    {
        return await Context.ToolSyncConfigurations
            .Include(tsc => tsc.ExecutionLogs.OrderByDescending(el => el.StartedAt).Take(5))
            .Where(tsc => tsc.ToolConnectionId == toolConnectionId)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<ToolSyncConfiguration>> GetEnabledConfigurationsAsync(CancellationToken cancellationToken = default)
    {
        return await Context.ToolSyncConfigurations
            .Where(tsc => tsc.IsEnabled)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<ToolSyncConfiguration>> GetConfigurationsReadyForSyncAsync(CancellationToken cancellationToken = default)
    {
        var now = DateTime.UtcNow;
        return await Context.ToolSyncConfigurations
            .Where(tsc => tsc.IsEnabled && 
                         tsc.NextSyncAt.HasValue && 
                         tsc.NextSyncAt.Value <= now &&
                         tsc.ConsecutiveFailures < 5)
            .OrderBy(tsc => tsc.NextSyncAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<ToolSyncConfiguration>> GetByEntityTypeAsync(string entityType, CancellationToken cancellationToken = default)
    {
        return await Context.ToolSyncConfigurations
            .Where(tsc => tsc.EntityType == entityType)
            .ToListAsync(cancellationToken);
    }

    public async Task<ToolSyncConfiguration?> GetByNameAsync(string name, CancellationToken cancellationToken = default)
    {
        return await Context.ToolSyncConfigurations
            .Include(tsc => tsc.ExecutionLogs.OrderByDescending(el => el.StartedAt).Take(5))
            .FirstOrDefaultAsync(tsc => tsc.Name == name, cancellationToken);
    }
}

public class SyncExecutionLogRepository : Repository<SyncExecutionLog>, ISyncExecutionLogRepository
{
    public SyncExecutionLogRepository(HeyDavDbContext context) : base(context)
    {
    }

    public async Task<IEnumerable<SyncExecutionLog>> GetBySyncConfigurationIdAsync(Guid syncConfigurationId, CancellationToken cancellationToken = default)
    {
        return await Context.SyncExecutionLogs
            .Where(sel => sel.SyncConfigurationId == syncConfigurationId)
            .OrderByDescending(sel => sel.StartedAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<SyncExecutionLog>> GetRunningExecutionsAsync(CancellationToken cancellationToken = default)
    {
        return await Context.SyncExecutionLogs
            .Where(sel => sel.Status == SyncExecutionStatus.Running)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<SyncExecutionLog>> GetRecentExecutionsAsync(TimeSpan timeSpan, CancellationToken cancellationToken = default)
    {
        var cutoffTime = DateTime.UtcNow - timeSpan;
        return await Context.SyncExecutionLogs
            .Where(sel => sel.StartedAt >= cutoffTime)
            .OrderByDescending(sel => sel.StartedAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<SyncExecutionLog?> GetLatestExecutionAsync(Guid syncConfigurationId, CancellationToken cancellationToken = default)
    {
        return await Context.SyncExecutionLogs
            .Where(sel => sel.SyncConfigurationId == syncConfigurationId)
            .OrderByDescending(sel => sel.StartedAt)
            .FirstOrDefaultAsync(cancellationToken);
    }

    public async Task<IEnumerable<SyncExecutionLog>> GetFailedExecutionsAsync(CancellationToken cancellationToken = default)
    {
        return await Context.SyncExecutionLogs
            .Where(sel => sel.Status == SyncExecutionStatus.Failed)
            .OrderByDescending(sel => sel.StartedAt)
            .ToListAsync(cancellationToken);
    }

    public async Task DeleteOldLogsAsync(TimeSpan maxAge, CancellationToken cancellationToken = default)
    {
        var cutoffTime = DateTime.UtcNow - maxAge;
        var oldLogs = await Context.SyncExecutionLogs
            .Where(sel => sel.StartedAt < cutoffTime && sel.Status != SyncExecutionStatus.Running)
            .ToListAsync(cancellationToken);

        Context.SyncExecutionLogs.RemoveRange(oldLogs);
        await Context.SaveChangesAsync(cancellationToken);
    }
}