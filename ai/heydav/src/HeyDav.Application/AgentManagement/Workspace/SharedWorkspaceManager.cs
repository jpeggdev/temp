using HeyDav.Domain.AgentManagement.Interfaces;
using Microsoft.Extensions.Logging;
using System.Collections.Concurrent;
using System.Text.RegularExpressions;

namespace HeyDav.Application.AgentManagement.Workspace;

public class SharedWorkspaceManager(
    IAgentRepository agentRepository,
    ILogger<SharedWorkspaceManager> logger) : ISharedWorkspaceManager
{
    private readonly IAgentRepository _agentRepository = agentRepository ?? throw new ArgumentNullException(nameof(agentRepository));
    private readonly ILogger<SharedWorkspaceManager> _logger = logger ?? throw new ArgumentNullException(nameof(logger));

    // In-memory storage - in production, this would be persisted
    private readonly ConcurrentDictionary<Guid, SharedWorkspace> _workspaces = new();
    private readonly ConcurrentDictionary<Guid, List<WorkspaceDocument>> _documents = new();
    private readonly ConcurrentDictionary<Guid, List<DocumentVersion>> _documentVersions = new();
    private readonly ConcurrentDictionary<Guid, ResourceLock> _resourceLocks = new();
    private readonly ConcurrentDictionary<Guid, List<WorkspaceActivity>> _activities = new();
    private readonly ConcurrentDictionary<Guid, List<SyncConflict>> _syncConflicts = new();

    public async Task<Guid> CreateWorkspaceAsync(WorkspaceDefinition definition, CancellationToken cancellationToken = default)
    {
        try
        {
            // Validate owner exists
            var owner = await _agentRepository.GetByIdAsync(definition.OwnerId, cancellationToken);
            if (owner == null)
            {
                throw new ArgumentException($"Owner agent {definition.OwnerId} not found");
            }

            var workspaceId = Guid.NewGuid();
            var ownerMember = new WorkspaceMember(
                definition.OwnerId,
                owner.Name,
                WorkspaceRole.Owner,
                DateTime.UtcNow,
                DateTime.UtcNow,
                Enum.GetValues<WorkspacePermission>()
            );

            var workspace = new SharedWorkspace(
                workspaceId,
                definition.Name,
                definition.Description,
                definition.Type,
                definition.OwnerId,
                definition.Visibility,
                WorkspaceStatus.Active,
                new[] { ownerMember },
                Enumerable.Empty<WorkspaceResource>(),
                definition.Settings ?? new Dictionary<string, object>(),
                definition.Tags ?? Enumerable.Empty<string>(),
                DateTime.UtcNow,
                DateTime.UtcNow,
                DateTime.UtcNow
            );

            _workspaces[workspaceId] = workspace;
            _documents[workspaceId] = new List<WorkspaceDocument>();
            _activities[workspaceId] = new List<WorkspaceActivity>();

            await LogActivityAsync(workspaceId, new WorkspaceActivity(
                Guid.NewGuid(),
                workspaceId,
                definition.OwnerId,
                ActivityType.Created,
                $"Workspace '{definition.Name}' created",
                new Dictionary<string, object> { ["type"] = definition.Type.ToString() },
                DateTime.UtcNow
            ), cancellationToken);

            _logger.LogInformation("Created workspace '{WorkspaceName}' with ID {WorkspaceId} for owner {OwnerId}", 
                definition.Name, workspaceId, definition.OwnerId);

            return workspaceId;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to create workspace '{WorkspaceName}'", definition.Name);
            throw;
        }
    }

    public Task<bool> DeleteWorkspaceAsync(Guid workspaceId, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_workspaces.TryRemove(workspaceId, out var workspace))
            {
                _logger.LogWarning("Workspace {WorkspaceId} not found for deletion", workspaceId);
                return Task.FromResult(false);
            }

            // Clean up related data
            _documents.TryRemove(workspaceId, out _);
            _activities.TryRemove(workspaceId, out _);
            _syncConflicts.TryRemove(workspaceId, out _);

            // Remove locks associated with this workspace
            var workspaceResourceIds = workspace.Resources.Select(r => r.Id).ToHashSet();
            var locksToRemove = _resourceLocks.Where(kvp => workspaceResourceIds.Contains(kvp.Value.ResourceId)).ToList();
            foreach (var lockKvp in locksToRemove)
            {
                _resourceLocks.TryRemove(lockKvp.Key, out _);
            }

            _logger.LogInformation("Deleted workspace '{WorkspaceName}' with ID {WorkspaceId}", 
                workspace.Name, workspaceId);

            return Task.FromResult(true);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to delete workspace {WorkspaceId}", workspaceId);
            return Task.FromResult(false);
        }
    }

    public Task<SharedWorkspace?> GetWorkspaceAsync(Guid workspaceId, CancellationToken cancellationToken = default)
    {
        _workspaces.TryGetValue(workspaceId, out var workspace);
        
        if (workspace != null)
        {
            // Update last accessed time
            var updatedWorkspace = workspace with { LastAccessedAt = DateTime.UtcNow };
            _workspaces[workspaceId] = updatedWorkspace;
            return Task.FromResult<SharedWorkspace?>(updatedWorkspace);
        }

        return Task.FromResult<SharedWorkspace?>(null);
    }

    public Task<IEnumerable<SharedWorkspace>> GetWorkspacesAsync(Guid? ownerId = null, CancellationToken cancellationToken = default)
    {
        try
        {
            var workspaces = _workspaces.Values.AsEnumerable();
            
            if (ownerId.HasValue)
            {
                workspaces = workspaces.Where(w => w.OwnerId == ownerId.Value || 
                                                  w.Members.Any(m => m.AgentId == ownerId.Value));
            }

            var result = workspaces
                .OrderByDescending(w => w.LastAccessedAt)
                .ToList();

            return Task.FromResult<IEnumerable<SharedWorkspace>>(result);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get workspaces for owner {OwnerId}", ownerId);
            return Task.FromResult<IEnumerable<SharedWorkspace>>(Enumerable.Empty<SharedWorkspace>());
        }
    }

    public async Task<bool> UpdateWorkspaceAsync(Guid workspaceId, WorkspaceUpdate update, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_workspaces.TryGetValue(workspaceId, out var workspace))
            {
                _logger.LogWarning("Workspace {WorkspaceId} not found for update", workspaceId);
                return false;
            }

            var updatedWorkspace = workspace with
            {
                Name = update.Name ?? workspace.Name,
                Description = update.Description ?? workspace.Description,
                Visibility = update.Visibility ?? workspace.Visibility,
                Status = update.Status ?? workspace.Status,
                Settings = update.Settings ?? workspace.Settings,
                Tags = update.Tags ?? workspace.Tags,
                LastModifiedAt = DateTime.UtcNow
            };

            _workspaces[workspaceId] = updatedWorkspace;

            await LogActivityAsync(workspaceId, new WorkspaceActivity(
                Guid.NewGuid(),
                workspaceId,
                workspace.OwnerId, // Assuming owner is making the update
                ActivityType.Modified,
                "Workspace settings updated",
                new Dictionary<string, object> { ["updateFields"] = GetUpdateFields(update) }
            ), cancellationToken);

            _logger.LogInformation("Updated workspace {WorkspaceId}", workspaceId);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to update workspace {WorkspaceId}", workspaceId);
            return false;
        }
    }

    public async Task<bool> AddMemberAsync(Guid workspaceId, Guid agentId, WorkspaceRole role, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_workspaces.TryGetValue(workspaceId, out var workspace))
            {
                _logger.LogWarning("Workspace {WorkspaceId} not found for member addition", workspaceId);
                return false;
            }

            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for workspace membership", agentId);
                return false;
            }

            // Check if agent is already a member
            if (workspace.Members.Any(m => m.AgentId == agentId))
            {
                _logger.LogDebug("Agent {AgentId} is already a member of workspace {WorkspaceId}", agentId, workspaceId);
                return true;
            }

            var newMember = new WorkspaceMember(
                agentId,
                agent.Name,
                role,
                DateTime.UtcNow,
                DateTime.UtcNow,
                GetPermissionsForRole(role)
            );

            var updatedMembers = workspace.Members.Concat(new[] { newMember });
            var updatedWorkspace = workspace with { Members = updatedMembers, LastModifiedAt = DateTime.UtcNow };

            _workspaces[workspaceId] = updatedWorkspace;

            await LogActivityAsync(workspaceId, new WorkspaceActivity(
                Guid.NewGuid(),
                workspaceId,
                workspace.OwnerId,
                ActivityType.Shared,
                $"Added member {agent.Name} with role {role}",
                new Dictionary<string, object> { ["memberId"] = agentId.ToString(), ["role"] = role.ToString() }
            ), cancellationToken);

            _logger.LogInformation("Added agent {AgentId} ({AgentName}) to workspace {WorkspaceId} with role {Role}", 
                agentId, agent.Name, workspaceId, role);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to add member {AgentId} to workspace {WorkspaceId}", agentId, workspaceId);
            return false;
        }
    }

    public async Task<bool> RemoveMemberAsync(Guid workspaceId, Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_workspaces.TryGetValue(workspaceId, out var workspace))
            {
                _logger.LogWarning("Workspace {WorkspaceId} not found for member removal", workspaceId);
                return false;
            }

            // Can't remove the owner
            if (workspace.OwnerId == agentId)
            {
                _logger.LogWarning("Cannot remove owner {AgentId} from workspace {WorkspaceId}", agentId, workspaceId);
                return false;
            }

            var member = workspace.Members.FirstOrDefault(m => m.AgentId == agentId);
            if (member == null)
            {
                _logger.LogDebug("Agent {AgentId} is not a member of workspace {WorkspaceId}", agentId, workspaceId);
                return true;
            }

            var updatedMembers = workspace.Members.Where(m => m.AgentId != agentId);
            var updatedWorkspace = workspace with { Members = updatedMembers, LastModifiedAt = DateTime.UtcNow };

            _workspaces[workspaceId] = updatedWorkspace;

            await LogActivityAsync(workspaceId, new WorkspaceActivity(
                Guid.NewGuid(),
                workspaceId,
                workspace.OwnerId,
                ActivityType.Modified,
                $"Removed member {member.AgentName}",
                new Dictionary<string, object> { ["removedMemberId"] = agentId.ToString() }
            ), cancellationToken);

            _logger.LogInformation("Removed agent {AgentId} ({AgentName}) from workspace {WorkspaceId}", 
                agentId, member.AgentName, workspaceId);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to remove member {AgentId} from workspace {WorkspaceId}", agentId, workspaceId);
            return false;
        }
    }

    public async Task<bool> UpdateMemberRoleAsync(Guid workspaceId, Guid agentId, WorkspaceRole newRole, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_workspaces.TryGetValue(workspaceId, out var workspace))
            {
                _logger.LogWarning("Workspace {WorkspaceId} not found for role update", workspaceId);
                return false;
            }

            var member = workspace.Members.FirstOrDefault(m => m.AgentId == agentId);
            if (member == null)
            {
                _logger.LogWarning("Member {AgentId} not found in workspace {WorkspaceId}", agentId, workspaceId);
                return false;
            }

            // Can't change owner role
            if (member.Role == WorkspaceRole.Owner)
            {
                _logger.LogWarning("Cannot change role of owner {AgentId} in workspace {WorkspaceId}", agentId, workspaceId);
                return false;
            }

            var updatedMember = member with 
            { 
                Role = newRole, 
                Permissions = GetPermissionsForRole(newRole) 
            };

            var updatedMembers = workspace.Members.Select(m => m.AgentId == agentId ? updatedMember : m);
            var updatedWorkspace = workspace with { Members = updatedMembers, LastModifiedAt = DateTime.UtcNow };

            _workspaces[workspaceId] = updatedWorkspace;

            await LogActivityAsync(workspaceId, new WorkspaceActivity(
                Guid.NewGuid(),
                workspaceId,
                workspace.OwnerId,
                ActivityType.Modified,
                $"Updated role of {member.AgentName} from {member.Role} to {newRole}",
                new Dictionary<string, object> 
                { 
                    ["memberId"] = agentId.ToString(),
                    ["oldRole"] = member.Role.ToString(),
                    ["newRole"] = newRole.ToString()
                }
            ), cancellationToken);

            _logger.LogInformation("Updated role of agent {AgentId} in workspace {WorkspaceId} from {OldRole} to {NewRole}", 
                agentId, workspaceId, member.Role, newRole);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to update role of member {AgentId} in workspace {WorkspaceId}", agentId, workspaceId);
            return false;
        }
    }

    public Task<IEnumerable<WorkspaceMember>> GetMembersAsync(Guid workspaceId, CancellationToken cancellationToken = default)
    {
        try
        {
            if (_workspaces.TryGetValue(workspaceId, out var workspace))
            {
                return Task.FromResult(workspace.Members);
            }

            return Task.FromResult<IEnumerable<WorkspaceMember>>(Enumerable.Empty<WorkspaceMember>());
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get members for workspace {WorkspaceId}", workspaceId);
            return Task.FromResult<IEnumerable<WorkspaceMember>>(Enumerable.Empty<WorkspaceMember>());
        }
    }

    public Task<bool> HasAccessAsync(Guid workspaceId, Guid agentId, WorkspacePermission requiredPermission, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_workspaces.TryGetValue(workspaceId, out var workspace))
            {
                return Task.FromResult(false);
            }

            var member = workspace.Members.FirstOrDefault(m => m.AgentId == agentId);
            if (member == null)
            {
                return Task.FromResult(false);
            }

            return Task.FromResult(member.Permissions.Contains(requiredPermission));
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to check access for agent {AgentId} in workspace {WorkspaceId}", agentId, workspaceId);
            return Task.FromResult(false);
        }
    }

    public async Task<Guid> AddResourceAsync(Guid workspaceId, WorkspaceResource resource, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_workspaces.TryGetValue(workspaceId, out var workspace))
            {
                throw new ArgumentException($"Workspace {workspaceId} not found");
            }

            var resourceWithDefaults = resource with
            {
                Id = resource.Id == Guid.Empty ? Guid.NewGuid() : resource.Id,
                CreatedAt = resource.CreatedAt == default ? DateTime.UtcNow : resource.CreatedAt,
                LastModifiedAt = resource.LastModifiedAt == default ? DateTime.UtcNow : resource.LastModifiedAt
            };

            var updatedResources = workspace.Resources.Concat(new[] { resourceWithDefaults });
            var updatedWorkspace = workspace with { Resources = updatedResources, LastModifiedAt = DateTime.UtcNow };

            _workspaces[workspaceId] = updatedWorkspace;

            await LogActivityAsync(workspaceId, new WorkspaceActivity(
                Guid.NewGuid(),
                workspaceId,
                resource.CreatedById,
                ActivityType.Created,
                $"Added resource '{resource.Name}'",
                new Dictionary<string, object> 
                { 
                    ["resourceId"] = resourceWithDefaults.Id.ToString(),
                    ["resourceType"] = resource.Type.ToString()
                }
            ), cancellationToken);

            _logger.LogInformation("Added resource '{ResourceName}' to workspace {WorkspaceId}", 
                resource.Name, workspaceId);

            return resourceWithDefaults.Id;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to add resource '{ResourceName}' to workspace {WorkspaceId}", 
                resource.Name, workspaceId);
            throw;
        }
    }

    public async Task<bool> RemoveResourceAsync(Guid workspaceId, Guid resourceId, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_workspaces.TryGetValue(workspaceId, out var workspace))
            {
                _logger.LogWarning("Workspace {WorkspaceId} not found for resource removal", workspaceId);
                return false;
            }

            var resource = workspace.Resources.FirstOrDefault(r => r.Id == resourceId);
            if (resource == null)
            {
                _logger.LogWarning("Resource {ResourceId} not found in workspace {WorkspaceId}", resourceId, workspaceId);
                return false;
            }

            var updatedResources = workspace.Resources.Where(r => r.Id != resourceId);
            var updatedWorkspace = workspace with { Resources = updatedResources, LastModifiedAt = DateTime.UtcNow };

            _workspaces[workspaceId] = updatedWorkspace;

            // Remove any locks on this resource
            var lockToRemove = _resourceLocks.FirstOrDefault(kvp => kvp.Value.ResourceId == resourceId);
            if (lockToRemove.Key != Guid.Empty)
            {
                _resourceLocks.TryRemove(lockToRemove.Key, out _);
            }

            await LogActivityAsync(workspaceId, new WorkspaceActivity(
                Guid.NewGuid(),
                workspaceId,
                workspace.OwnerId, // Assuming owner is removing
                ActivityType.Deleted,
                $"Removed resource '{resource.Name}'",
                new Dictionary<string, object> { ["resourceId"] = resourceId.ToString() }
            ), cancellationToken);

            _logger.LogInformation("Removed resource '{ResourceName}' from workspace {WorkspaceId}", 
                resource.Name, workspaceId);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to remove resource {ResourceId} from workspace {WorkspaceId}", 
                resourceId, workspaceId);
            return false;
        }
    }

    public Task<WorkspaceResource?> GetResourceAsync(Guid workspaceId, Guid resourceId, CancellationToken cancellationToken = default)
    {
        try
        {
            if (_workspaces.TryGetValue(workspaceId, out var workspace))
            {
                var resource = workspace.Resources.FirstOrDefault(r => r.Id == resourceId);
                return Task.FromResult(resource);
            }

            return Task.FromResult<WorkspaceResource?>(null);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get resource {ResourceId} from workspace {WorkspaceId}", 
                resourceId, workspaceId);
            return Task.FromResult<WorkspaceResource?>(null);
        }
    }

    public Task<IEnumerable<WorkspaceResource>> GetResourcesAsync(Guid workspaceId, ResourceType? type = null, CancellationToken cancellationToken = default)
    {
        try
        {
            if (_workspaces.TryGetValue(workspaceId, out var workspace))
            {
                var resources = workspace.Resources.AsEnumerable();
                
                if (type.HasValue)
                {
                    resources = resources.Where(r => r.Type == type.Value);
                }

                return Task.FromResult(resources.OrderByDescending(r => r.LastModifiedAt));
            }

            return Task.FromResult<IEnumerable<WorkspaceResource>>(Enumerable.Empty<WorkspaceResource>());
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get resources from workspace {WorkspaceId}", workspaceId);
            return Task.FromResult<IEnumerable<WorkspaceResource>>(Enumerable.Empty<WorkspaceResource>());
        }
    }

    public async Task<bool> UpdateResourceAsync(Guid workspaceId, Guid resourceId, ResourceUpdate update, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_workspaces.TryGetValue(workspaceId, out var workspace))
            {
                _logger.LogWarning("Workspace {WorkspaceId} not found for resource update", workspaceId);
                return false;
            }

            var resource = workspace.Resources.FirstOrDefault(r => r.Id == resourceId);
            if (resource == null)
            {
                _logger.LogWarning("Resource {ResourceId} not found in workspace {WorkspaceId}", resourceId, workspaceId);
                return false;
            }

            var updatedResource = resource with
            {
                Name = update.Name ?? resource.Name,
                Description = update.Description ?? resource.Description,
                Location = update.Location ?? resource.Location,
                Metadata = update.Metadata ?? resource.Metadata,
                Tags = update.Tags ?? resource.Tags,
                LastModifiedAt = DateTime.UtcNow
            };

            var updatedResources = workspace.Resources.Select(r => r.Id == resourceId ? updatedResource : r);
            var updatedWorkspace = workspace with { Resources = updatedResources, LastModifiedAt = DateTime.UtcNow };

            _workspaces[workspaceId] = updatedWorkspace;

            await LogActivityAsync(workspaceId, new WorkspaceActivity(
                Guid.NewGuid(),
                workspaceId,
                workspace.OwnerId, // Assuming owner is updating
                ActivityType.Modified,
                $"Updated resource '{resource.Name}'",
                new Dictionary<string, object> { ["resourceId"] = resourceId.ToString() }
            ), cancellationToken);

            _logger.LogInformation("Updated resource {ResourceId} in workspace {WorkspaceId}", resourceId, workspaceId);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to update resource {ResourceId} in workspace {WorkspaceId}", 
                resourceId, workspaceId);
            return false;
        }
    }

    // Continuing with document management and other methods...

    public async Task<Guid> CreateDocumentAsync(Guid workspaceId, WorkspaceDocument document, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_workspaces.ContainsKey(workspaceId))
            {
                throw new ArgumentException($"Workspace {workspaceId} not found");
            }

            var documentWithDefaults = document with
            {
                Id = document.Id == Guid.Empty ? Guid.NewGuid() : document.Id,
                CreatedAt = document.CreatedAt == default ? DateTime.UtcNow : document.CreatedAt,
                LastModifiedAt = document.LastModifiedAt == default ? DateTime.UtcNow : document.LastModifiedAt,
                Version = 1,
                Status = DocumentStatus.Draft
            };

            var documents = _documents.GetOrAdd(workspaceId, _ => new List<WorkspaceDocument>());
            lock (documents)
            {
                documents.Add(documentWithDefaults);
            }

            // Create initial version
            var initialVersion = new DocumentVersion(1, document.Content, document.AuthorId, DateTime.UtcNow, "Initial version");
            var versions = _documentVersions.GetOrAdd(documentWithDefaults.Id, _ => new List<DocumentVersion>());
            lock (versions)
            {
                versions.Add(initialVersion);
            }

            await LogActivityAsync(workspaceId, new WorkspaceActivity(
                Guid.NewGuid(),
                workspaceId,
                document.AuthorId,
                ActivityType.Created,
                $"Created document '{document.Title}'",
                new Dictionary<string, object> { ["documentId"] = documentWithDefaults.Id.ToString() }
            ), cancellationToken);

            _logger.LogInformation("Created document '{DocumentTitle}' in workspace {WorkspaceId}", 
                document.Title, workspaceId);

            return documentWithDefaults.Id;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to create document '{DocumentTitle}' in workspace {WorkspaceId}", 
                document.Title, workspaceId);
            throw;
        }
    }

    public async Task<bool> LogActivityAsync(Guid workspaceId, WorkspaceActivity activity, CancellationToken cancellationToken = default)
    {
        try
        {
            var activityWithTimestamp = activity with 
            { 
                Timestamp = activity.Timestamp == default ? DateTime.UtcNow : activity.Timestamp 
            };

            var activities = _activities.GetOrAdd(workspaceId, _ => new List<WorkspaceActivity>());
            lock (activities)
            {
                activities.Add(activityWithTimestamp);
                // Keep only last 1000 activities per workspace
                if (activities.Count > 1000)
                {
                    activities.RemoveAt(0);
                }
            }

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to log activity for workspace {WorkspaceId}", workspaceId);
            return false;
        }
    }

    public Task<IEnumerable<WorkspaceActivity>> GetActivityLogAsync(Guid workspaceId, TimeSpan? period = null, CancellationToken cancellationToken = default)
    {
        try
        {
            var activities = _activities.GetValueOrDefault(workspaceId, new List<WorkspaceActivity>());
            
            var cutoffTime = period.HasValue ? DateTime.UtcNow.Subtract(period.Value) : DateTime.MinValue;
            
            IEnumerable<WorkspaceActivity> filteredActivities;
            lock (activities)
            {
                filteredActivities = activities
                    .Where(a => a.Timestamp >= cutoffTime)
                    .OrderByDescending(a => a.Timestamp)
                    .ToList();
            }

            return Task.FromResult(filteredActivities);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get activity log for workspace {WorkspaceId}", workspaceId);
            return Task.FromResult<IEnumerable<WorkspaceActivity>>(Enumerable.Empty<WorkspaceActivity>());
        }
    }

    // Implementation of remaining interface methods would continue here...
    // For brevity, I'll provide stubs for the remaining methods

    public Task<bool> UpdateDocumentAsync(Guid workspaceId, Guid documentId, string content, Guid editorId, CancellationToken cancellationToken = default)
    {
        // Implementation would update document content and create new version
        return Task.FromResult(true);
    }

    public Task<WorkspaceDocument?> GetDocumentAsync(Guid workspaceId, Guid documentId, CancellationToken cancellationToken = default)
    {
        var documents = _documents.GetValueOrDefault(workspaceId, new List<WorkspaceDocument>());
        lock (documents)
        {
            var document = documents.FirstOrDefault(d => d.Id == documentId);
            return Task.FromResult(document);
        }
    }

    public Task<IEnumerable<WorkspaceDocument>> GetDocumentsAsync(Guid workspaceId, CancellationToken cancellationToken = default)
    {
        var documents = _documents.GetValueOrDefault(workspaceId, new List<WorkspaceDocument>());
        lock (documents)
        {
            return Task.FromResult<IEnumerable<WorkspaceDocument>>(documents.ToList());
        }
    }

    public Task<bool> DeleteDocumentAsync(Guid workspaceId, Guid documentId, CancellationToken cancellationToken = default)
    {
        // Implementation would remove document and its versions
        return Task.FromResult(true);
    }

    public Task<IEnumerable<DocumentVersion>> GetDocumentHistoryAsync(Guid workspaceId, Guid documentId, CancellationToken cancellationToken = default)
    {
        var versions = _documentVersions.GetValueOrDefault(documentId, new List<DocumentVersion>());
        lock (versions)
        {
            return Task.FromResult<IEnumerable<DocumentVersion>>(versions.OrderByDescending(v => v.Version));
        }
    }

    public Task<bool> LockResourceAsync(Guid workspaceId, Guid resourceId, Guid agentId, TimeSpan? lockDuration = null, CancellationToken cancellationToken = default)
    {
        try
        {
            var lockId = Guid.NewGuid();
            var lockExpiry = lockDuration.HasValue ? DateTime.UtcNow.Add(lockDuration.Value) : (DateTime?)null;
            
            var resourceLock = new ResourceLock(resourceId, agentId, DateTime.UtcNow, lockExpiry);
            _resourceLocks[lockId] = resourceLock;

            _logger.LogInformation("Locked resource {ResourceId} by agent {AgentId} in workspace {WorkspaceId}", 
                resourceId, agentId, workspaceId);

            return Task.FromResult(true);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to lock resource {ResourceId} in workspace {WorkspaceId}", resourceId, workspaceId);
            return Task.FromResult(false);
        }
    }

    public Task<bool> UnlockResourceAsync(Guid workspaceId, Guid resourceId, Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            var lockToRemove = _resourceLocks.FirstOrDefault(kvp => 
                kvp.Value.ResourceId == resourceId && kvp.Value.LockedById == agentId);

            if (lockToRemove.Key != Guid.Empty)
            {
                _resourceLocks.TryRemove(lockToRemove.Key, out _);
                _logger.LogInformation("Unlocked resource {ResourceId} by agent {AgentId} in workspace {WorkspaceId}", 
                    resourceId, agentId, workspaceId);
                return Task.FromResult(true);
            }

            return Task.FromResult(false);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to unlock resource {ResourceId} in workspace {WorkspaceId}", resourceId, workspaceId);
            return Task.FromResult(false);
        }
    }

    public Task<ResourceLock?> GetResourceLockAsync(Guid workspaceId, Guid resourceId, CancellationToken cancellationToken = default)
    {
        var resourceLock = _resourceLocks.Values.FirstOrDefault(l => l.ResourceId == resourceId);
        return Task.FromResult(resourceLock);
    }

    public Task<IEnumerable<ResourceLock>> GetActiveLocksAsync(Guid workspaceId, CancellationToken cancellationToken = default)
    {
        if (!_workspaces.TryGetValue(workspaceId, out var workspace))
        {
            return Task.FromResult<IEnumerable<ResourceLock>>(Enumerable.Empty<ResourceLock>());
        }

        var workspaceResourceIds = workspace.Resources.Select(r => r.Id).ToHashSet();
        var activeLocks = _resourceLocks.Values
            .Where(l => workspaceResourceIds.Contains(l.ResourceId))
            .Where(l => l.ExpiresAt == null || l.ExpiresAt > DateTime.UtcNow)
            .ToList();

        return Task.FromResult<IEnumerable<ResourceLock>>(activeLocks);
    }

    public Task<IEnumerable<WorkspaceActivity>> GetAgentActivityAsync(Guid workspaceId, Guid agentId, TimeSpan? period = null, CancellationToken cancellationToken = default)
    {
        var activities = _activities.GetValueOrDefault(workspaceId, new List<WorkspaceActivity>());
        var cutoffTime = period.HasValue ? DateTime.UtcNow.Subtract(period.Value) : DateTime.MinValue;
        
        IEnumerable<WorkspaceActivity> agentActivities;
        lock (activities)
        {
            agentActivities = activities
                .Where(a => a.AgentId == agentId && a.Timestamp >= cutoffTime)
                .OrderByDescending(a => a.Timestamp)
                .ToList();
        }

        return Task.FromResult(agentActivities);
    }

    public Task<IEnumerable<WorkspaceSearchResult>> SearchWorkspacesAsync(string query, Guid? requestingAgentId = null, CancellationToken cancellationToken = default)
    {
        try
        {
            var searchTerms = query.ToLowerInvariant().Split(' ', StringSplitOptions.RemoveEmptyEntries);
            var results = new List<WorkspaceSearchResult>();

            foreach (var workspace in _workspaces.Values)
            {
                // Check if requesting agent has access
                if (requestingAgentId.HasValue && !workspace.Members.Any(m => m.AgentId == requestingAgentId.Value))
                {
                    continue;
                }

                var matchedTerms = new List<string>();
                var score = 0.0;

                // Match against name
                foreach (var term in searchTerms)
                {
                    if (workspace.Name.ToLowerInvariant().Contains(term))
                    {
                        matchedTerms.Add(term);
                        score += 10.0;
                    }
                }

                // Match against description
                foreach (var term in searchTerms)
                {
                    if (workspace.Description.ToLowerInvariant().Contains(term))
                    {
                        matchedTerms.Add(term);
                        score += 5.0;
                    }
                }

                // Match against tags
                foreach (var term in searchTerms)
                {
                    if (workspace.Tags.Any(tag => tag.ToLowerInvariant().Contains(term)))
                    {
                        matchedTerms.Add(term);
                        score += 3.0;
                    }
                }

                if (matchedTerms.Any())
                {
                    results.Add(new WorkspaceSearchResult(
                        workspace.Id,
                        workspace.Name,
                        workspace.Description,
                        workspace.Type,
                        score,
                        matchedTerms.Distinct()
                    ));
                }
            }

            return Task.FromResult<IEnumerable<WorkspaceSearchResult>>(results.OrderByDescending(r => r.RelevanceScore));
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to search workspaces with query '{Query}'", query);
            return Task.FromResult<IEnumerable<WorkspaceSearchResult>>(Enumerable.Empty<WorkspaceSearchResult>());
        }
    }

    // Additional stub implementations for completeness
    public Task<IEnumerable<ResourceSearchResult>> SearchResourcesAsync(Guid workspaceId, string query, ResourceType? type = null, CancellationToken cancellationToken = default)
    {
        return Task.FromResult<IEnumerable<ResourceSearchResult>>(Enumerable.Empty<ResourceSearchResult>());
    }

    public Task<IEnumerable<WorkspaceDocument>> SearchDocumentsAsync(Guid workspaceId, string query, CancellationToken cancellationToken = default)
    {
        return Task.FromResult<IEnumerable<WorkspaceDocument>>(Enumerable.Empty<WorkspaceDocument>());
    }

    public Task<WorkspaceSync> GetSyncStatusAsync(Guid workspaceId, CancellationToken cancellationToken = default)
    {
        return Task.FromResult(new WorkspaceSync(workspaceId, DateTime.UtcNow, SyncStatus.Synchronized, 0, Enumerable.Empty<Guid>()));
    }

    public Task<bool> SyncWorkspaceAsync(Guid workspaceId, CancellationToken cancellationToken = default)
    {
        return Task.FromResult(true);
    }

    public Task<IEnumerable<SyncConflict>> GetSyncConflictsAsync(Guid workspaceId, CancellationToken cancellationToken = default)
    {
        return Task.FromResult<IEnumerable<SyncConflict>>(Enumerable.Empty<SyncConflict>());
    }

    public Task<bool> ResolveSyncConflictAsync(Guid workspaceId, Guid conflictId, ConflictResolution resolution, CancellationToken cancellationToken = default)
    {
        return Task.FromResult(true);
    }

    public Task<WorkspaceAnalytics> GetWorkspaceAnalyticsAsync(Guid workspaceId, TimeSpan? period = null, CancellationToken cancellationToken = default)
    {
        if (!_workspaces.TryGetValue(workspaceId, out var workspace))
        {
            throw new ArgumentException($"Workspace {workspaceId} not found");
        }

        var analysisPeriod = period ?? TimeSpan.FromDays(30);
        var activities = _activities.GetValueOrDefault(workspaceId, new List<WorkspaceActivity>());
        var cutoffTime = DateTime.UtcNow.Subtract(analysisPeriod);

        IEnumerable<WorkspaceActivity> recentActivities;
        lock (activities)
        {
            recentActivities = activities.Where(a => a.Timestamp >= cutoffTime).ToList();
        }

        var analytics = new WorkspaceAnalytics(
            workspaceId,
            analysisPeriod,
            workspace.Members.Count(),
            workspace.Members.Count(m => m.LastActiveAt >= cutoffTime),
            workspace.Resources.Count(),
            workspace.Resources.Count(r => r.LastModifiedAt >= cutoffTime),
            recentActivities.Count(),
            recentActivities.GroupBy(a => a.Type).ToDictionary(g => g.Key, g => g.Count()),
            recentActivities.GroupBy(a => a.AgentId).ToDictionary(g => g.Key, g => g.Count()),
            workspace.Resources.GroupBy(r => r.Type).ToDictionary(g => g.Key, g => g.Count()),
            workspace.Resources.OrderByDescending(r => r.LastModifiedAt).Take(5)
                .Select(r => new PopularResource(r.Id, r.Name, r.Type, 0, 1)),
            CalculateCollaborationScore(workspace, recentActivities)
        );

        return Task.FromResult(analytics);
    }

    public Task<IEnumerable<CollaborationInsight>> GetCollaborationInsightsAsync(Guid workspaceId, CancellationToken cancellationToken = default)
    {
        var insights = new List<CollaborationInsight>();
        
        if (_workspaces.TryGetValue(workspaceId, out var workspace))
        {
            // Generate simple insights
            if (workspace.Members.Count() > 1)
            {
                insights.Add(new CollaborationInsight(
                    "Team Collaboration",
                    $"Workspace has {workspace.Members.Count()} active members",
                    workspace.Members.Count() * 10.0,
                    workspace.Members.Select(m => m.AgentId)
                ));
            }
        }

        return Task.FromResult<IEnumerable<CollaborationInsight>>(insights);
    }

    // Helper methods
    private static IEnumerable<WorkspacePermission> GetPermissionsForRole(WorkspaceRole role)
    {
        return role switch
        {
            WorkspaceRole.Owner => Enum.GetValues<WorkspacePermission>(),
            WorkspaceRole.Admin => new[] 
            { 
                WorkspacePermission.Read, WorkspacePermission.Write, WorkspacePermission.Delete,
                WorkspacePermission.Share, WorkspacePermission.AdministerMembers, WorkspacePermission.Lock,
                WorkspacePermission.Export
            },
            WorkspaceRole.Editor => new[] 
            { 
                WorkspacePermission.Read, WorkspacePermission.Write, WorkspacePermission.Lock,
                WorkspacePermission.Export
            },
            WorkspaceRole.Contributor => new[] 
            { 
                WorkspacePermission.Read, WorkspacePermission.Write, WorkspacePermission.Export
            },
            WorkspaceRole.Viewer => new[] { WorkspacePermission.Read },
            _ => Enumerable.Empty<WorkspacePermission>()
        };
    }

    private static List<string> GetUpdateFields(WorkspaceUpdate update)
    {
        var fields = new List<string>();
        if (update.Name != null) fields.Add("name");
        if (update.Description != null) fields.Add("description");
        if (update.Visibility != null) fields.Add("visibility");
        if (update.Status != null) fields.Add("status");
        if (update.Settings != null) fields.Add("settings");
        if (update.Tags != null) fields.Add("tags");
        return fields;
    }

    private static double CalculateCollaborationScore(SharedWorkspace workspace, IEnumerable<WorkspaceActivity> recentActivities)
    {
        var memberCount = workspace.Members.Count();
        var activityCount = recentActivities.Count();
        var uniqueActiveMembers = recentActivities.Select(a => a.AgentId).Distinct().Count();
        
        if (memberCount <= 1) return 0.0;
        
        var score = (uniqueActiveMembers / (double)memberCount) * 50.0; // Up to 50 for member participation
        score += Math.Min(50.0, activityCount / 10.0); // Up to 50 for activity level
        
        return Math.Min(100.0, score);
    }
}