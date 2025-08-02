using HeyDav.Domain.AgentManagement.Interfaces;
using Microsoft.Extensions.Logging;
using System.Collections.Concurrent;

namespace HeyDav.Application.AgentManagement.Communication;

public class AgentCommunicationHub(
    IAgentRepository agentRepository,
    ILogger<AgentCommunicationHub> logger) : IAgentCommunicationHub
{
    private readonly IAgentRepository _agentRepository = agentRepository ?? throw new ArgumentNullException(nameof(agentRepository));
    private readonly ILogger<AgentCommunicationHub> _logger = logger ?? throw new ArgumentNullException(nameof(logger));

    // In-memory storage - in production, this would be persisted
    private readonly ConcurrentDictionary<Guid, List<AgentMessage>> _agentMessages = new();
    private readonly ConcurrentDictionary<Guid, AgentMessage> _allMessages = new();
    private readonly ConcurrentDictionary<Guid, Conversation> _conversations = new();
    private readonly ConcurrentDictionary<Guid, AgentRequest> _requests = new();
    private readonly ConcurrentDictionary<Guid, AgentResponse> _responses = new();
    private readonly ConcurrentDictionary<Guid, AgentPresence> _agentPresences = new();
    private readonly ConcurrentDictionary<Guid, HashSet<AgentEventType>> _eventSubscriptions = new();
    private readonly ConcurrentDictionary<Guid, List<KnowledgeShare>> _knowledgeBase = new();
    private readonly ConcurrentDictionary<Guid, TaskDelegation> _delegations = new();
    private readonly ConcurrentDictionary<Guid, CollaborationRequest> _collaborations = new();

    public async Task<bool> SendMessageAsync(Guid fromAgentId, Guid toAgentId, AgentMessage message, CancellationToken cancellationToken = default)
    {
        try
        {
            // Validate agents exist
            var fromAgent = await _agentRepository.GetByIdAsync(fromAgentId, cancellationToken);
            var toAgent = await _agentRepository.GetByIdAsync(toAgentId, cancellationToken);

            if (fromAgent == null || toAgent == null)
            {
                _logger.LogWarning("Invalid agent IDs for message sending: From={FromAgentId}, To={ToAgentId}", fromAgentId, toAgentId);
                return false;
            }

            var messageWithTimestamp = message with 
            { 
                FromAgentId = fromAgentId,
                ToAgentId = toAgentId,
                Timestamp = message.Timestamp == default ? DateTime.UtcNow : message.Timestamp
            };

            // Store message
            _allMessages[messageWithTimestamp.Id] = messageWithTimestamp;
            
            // Add to recipient's message list
            var recipientMessages = _agentMessages.GetOrAdd(toAgentId, _ => new List<AgentMessage>());
            lock (recipientMessages)
            {
                recipientMessages.Add(messageWithTimestamp);
                // Keep only last 1000 messages per agent
                if (recipientMessages.Count > 1000)
                {
                    recipientMessages.RemoveAt(0);
                }
            }

            _logger.LogDebug("Message sent from agent {FromAgentId} to agent {ToAgentId}: {MessageType}", 
                fromAgentId, toAgentId, message.Type);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to send message from agent {FromAgentId} to agent {ToAgentId}", fromAgentId, toAgentId);
            return false;
        }
    }

    public async Task<bool> BroadcastMessageAsync(Guid fromAgentId, AgentMessage message, IEnumerable<Guid>? targetAgentIds = null, CancellationToken cancellationToken = default)
    {
        try
        {
            var targets = targetAgentIds?.ToList() ?? (await _agentRepository.GetActiveAgentsAsync(cancellationToken))
                .Where(a => a.Id != fromAgentId)
                .Select(a => a.Id)
                .ToList();

            var successCount = 0;
            foreach (var targetId in targets)
            {
                var targetMessage = message with { ToAgentId = targetId };
                if (await SendMessageAsync(fromAgentId, targetId, targetMessage, cancellationToken))
                {
                    successCount++;
                }
            }

            _logger.LogInformation("Broadcast message from agent {FromAgentId} to {TargetCount} agents, {SuccessCount} successful", 
                fromAgentId, targets.Count, successCount);

            return successCount > 0;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to broadcast message from agent {FromAgentId}", fromAgentId);
            return false;
        }
    }

    public async Task<bool> SendNotificationAsync(Guid agentId, AgentNotification notification, CancellationToken cancellationToken = default)
    {
        try
        {
            var agent = await _agentRepository.GetByIdAsync(agentId, cancellationToken);
            if (agent == null)
            {
                _logger.LogWarning("Agent {AgentId} not found for notification", agentId);
                return false;
            }

            // Convert notification to message
            var message = new AgentMessage(
                Guid.NewGuid(),
                Guid.Empty, // System sender
                agentId,
                notification.Content,
                MessageType.Alert,
                (MessagePriority)(int)notification.Priority,
                notification.Data != null ? new Dictionary<string, object>(notification.Data) : null,
                null,
                null,
                notification.Timestamp == default ? DateTime.UtcNow : notification.Timestamp
            );

            return await SendMessageAsync(Guid.Empty, agentId, message, cancellationToken);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to send notification to agent {AgentId}", agentId);
            return false;
        }
    }

    public Task<IEnumerable<AgentMessage>> GetMessagesAsync(Guid agentId, bool markAsRead = true, CancellationToken cancellationToken = default)
    {
        try
        {
            var messages = _agentMessages.GetValueOrDefault(agentId, new List<AgentMessage>());
            
            IEnumerable<AgentMessage> result;
            lock (messages)
            {
                result = messages.ToList();
                
                if (markAsRead)
                {
                    for (int i = 0; i < messages.Count; i++)
                    {
                        if (!messages[i].IsRead)
                        {
                            messages[i] = messages[i] with { IsRead = true };
                            _allMessages[messages[i].Id] = messages[i];
                        }
                    }
                }
            }

            return Task.FromResult(result);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get messages for agent {AgentId}", agentId);
            return Task.FromResult<IEnumerable<AgentMessage>>(Enumerable.Empty<AgentMessage>());
        }
    }

    public Task<IEnumerable<AgentMessage>> GetUnreadMessagesAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            var messages = _agentMessages.GetValueOrDefault(agentId, new List<AgentMessage>());
            
            IEnumerable<AgentMessage> unreadMessages;
            lock (messages)
            {
                unreadMessages = messages.Where(m => !m.IsRead).ToList();
            }

            return Task.FromResult(unreadMessages);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get unread messages for agent {AgentId}", agentId);
            return Task.FromResult<IEnumerable<AgentMessage>>(Enumerable.Empty<AgentMessage>());
        }
    }

    public Task<AgentMessage?> GetMessageAsync(Guid messageId, CancellationToken cancellationToken = default)
    {
        _allMessages.TryGetValue(messageId, out var message);
        return Task.FromResult(message);
    }

    public async Task<Guid> StartConversationAsync(string topic, IEnumerable<Guid> participantIds, ConversationType type = ConversationType.Collaboration, CancellationToken cancellationToken = default)
    {
        try
        {
            var participants = participantIds.ToList();
            
            // Validate all participants exist
            foreach (var participantId in participants)
            {
                var agent = await _agentRepository.GetByIdAsync(participantId, cancellationToken);
                if (agent == null)
                {
                    _logger.LogWarning("Agent {AgentId} not found for conversation", participantId);
                    participants.Remove(participantId);
                }
            }

            if (participants.Count < 2)
            {
                _logger.LogWarning("Insufficient valid participants for conversation. Required: 2, Found: {Count}", participants.Count);
                throw new ArgumentException("At least 2 valid participants required for conversation");
            }

            var conversationId = Guid.NewGuid();
            var conversation = new Conversation(
                conversationId,
                topic,
                type,
                participants,
                DateTime.UtcNow,
                DateTime.UtcNow,
                ConversationStatus.Active
            );

            _conversations[conversationId] = conversation;

            _logger.LogInformation("Started conversation '{Topic}' with {ParticipantCount} participants", topic, participants.Count);
            return conversationId;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to start conversation with topic '{Topic}'", topic);
            throw;
        }
    }

    public Task<bool> JoinConversationAsync(Guid conversationId, Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_conversations.TryGetValue(conversationId, out var conversation))
            {
                _logger.LogWarning("Conversation {ConversationId} not found", conversationId);
                return Task.FromResult(false);
            }

            if (conversation.ParticipantIds.Contains(agentId))
            {
                _logger.LogDebug("Agent {AgentId} is already in conversation {ConversationId}", agentId, conversationId);
                return Task.FromResult(true);
            }

            var updatedParticipants = conversation.ParticipantIds.Concat(new[] { agentId });
            var updatedConversation = conversation with 
            { 
                ParticipantIds = updatedParticipants,
                LastActivity = DateTime.UtcNow
            };

            _conversations[conversationId] = updatedConversation;

            _logger.LogInformation("Agent {AgentId} joined conversation {ConversationId}", agentId, conversationId);
            return Task.FromResult(true);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to join agent {AgentId} to conversation {ConversationId}", agentId, conversationId);
            return Task.FromResult(false);
        }
    }

    public Task<bool> LeaveConversationAsync(Guid conversationId, Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_conversations.TryGetValue(conversationId, out var conversation))
            {
                _logger.LogWarning("Conversation {ConversationId} not found", conversationId);
                return Task.FromResult(false);
            }

            var updatedParticipants = conversation.ParticipantIds.Where(id => id != agentId).ToList();
            
            if (updatedParticipants.Count < 2)
            {
                // Archive conversation if less than 2 participants remain
                var archivedConversation = conversation with 
                { 
                    Status = ConversationStatus.Archived,
                    LastActivity = DateTime.UtcNow
                };
                _conversations[conversationId] = archivedConversation;
            }
            else
            {
                var updatedConversation = conversation with 
                { 
                    ParticipantIds = updatedParticipants,
                    LastActivity = DateTime.UtcNow
                };
                _conversations[conversationId] = updatedConversation;
            }

            _logger.LogInformation("Agent {AgentId} left conversation {ConversationId}", agentId, conversationId);
            return Task.FromResult(true);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to remove agent {AgentId} from conversation {ConversationId}", agentId, conversationId);
            return Task.FromResult(false);
        }
    }

    public Task<Conversation?> GetConversationAsync(Guid conversationId, CancellationToken cancellationToken = default)
    {
        _conversations.TryGetValue(conversationId, out var conversation);
        return Task.FromResult(conversation);
    }

    public Task<IEnumerable<Conversation>> GetAgentConversationsAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            var agentConversations = _conversations.Values
                .Where(c => c.ParticipantIds.Contains(agentId))
                .OrderByDescending(c => c.LastActivity)
                .ToList();

            return Task.FromResult<IEnumerable<Conversation>>(agentConversations);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get conversations for agent {AgentId}", agentId);
            return Task.FromResult<IEnumerable<Conversation>>(Enumerable.Empty<Conversation>());
        }
    }

    public async Task<Guid> SendRequestAsync(Guid fromAgentId, Guid toAgentId, AgentRequest request, CancellationToken cancellationToken = default)
    {
        try
        {
            var requestWithDefaults = request with 
            { 
                FromAgentId = fromAgentId,
                ToAgentId = toAgentId,
                CreatedAt = request.CreatedAt == default ? DateTime.UtcNow : request.CreatedAt
            };

            _requests[requestWithDefaults.Id] = requestWithDefaults;

            // Send as message too
            var message = new AgentMessage(
                Guid.NewGuid(),
                fromAgentId,
                toAgentId,
                $"Request: {request.Description}",
                MessageType.Command,
                (MessagePriority)(int)request.Priority,
                new Dictionary<string, object> { ["requestId"] = request.Id.ToString() }
            );

            await SendMessageAsync(fromAgentId, toAgentId, message, cancellationToken);

            _logger.LogInformation("Request {RequestId} sent from agent {FromAgentId} to agent {ToAgentId}: {RequestType}", 
                request.Id, fromAgentId, toAgentId, request.RequestType);

            return requestWithDefaults.Id;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to send request from agent {FromAgentId} to agent {ToAgentId}", fromAgentId, toAgentId);
            throw;
        }
    }

    public async Task<bool> SendResponseAsync(Guid requestId, AgentResponse response, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_requests.TryGetValue(requestId, out var originalRequest))
            {
                _logger.LogWarning("Request {RequestId} not found for response", requestId);
                return false;
            }

            var responseWithDefaults = response with 
            { 
                RequestId = requestId,
                CreatedAt = response.CreatedAt == default ? DateTime.UtcNow : response.CreatedAt
            };

            _responses[responseWithDefaults.Id] = responseWithDefaults;

            // Send response as message
            var message = new AgentMessage(
                Guid.NewGuid(),
                response.FromAgentId,
                originalRequest.FromAgentId,
                response.Success ? "Request completed successfully" : $"Request failed: {response.ErrorMessage}",
                MessageType.Answer,
                MessagePriority.Medium,
                response.Data != null ? new Dictionary<string, object>(response.Data) : null
            );

            await SendMessageAsync(response.FromAgentId, originalRequest.FromAgentId, message, cancellationToken);

            _logger.LogInformation("Response sent for request {RequestId}: Success={Success}", requestId, response.Success);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to send response for request {RequestId}", requestId);
            return false;
        }
    }

    public async Task<AgentResponse?> WaitForResponseAsync(Guid requestId, TimeSpan timeout, CancellationToken cancellationToken = default)
    {
        try
        {
            var endTime = DateTime.UtcNow.Add(timeout);
            
            while (DateTime.UtcNow < endTime && !cancellationToken.IsCancellationRequested)
            {
                var response = _responses.Values.FirstOrDefault(r => r.RequestId == requestId);
                if (response != null)
                {
                    return response;
                }
                
                await Task.Delay(100, cancellationToken);
            }

            _logger.LogWarning("Timeout waiting for response to request {RequestId}", requestId);
            return null;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error waiting for response to request {RequestId}", requestId);
            return null;
        }
    }

    public Task<IEnumerable<AgentRequest>> GetPendingRequestsAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        try
        {
            var pendingRequests = _requests.Values
                .Where(r => r.ToAgentId == agentId)
                .Where(r => !_responses.Values.Any(resp => resp.RequestId == r.Id))
                .Where(r => r.Timeout == null || DateTime.UtcNow - r.CreatedAt < r.Timeout)
                .OrderByDescending(r => r.Priority)
                .ThenBy(r => r.CreatedAt)
                .ToList();

            return Task.FromResult<IEnumerable<AgentRequest>>(pendingRequests);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get pending requests for agent {AgentId}", agentId);
            return Task.FromResult<IEnumerable<AgentRequest>>(Enumerable.Empty<AgentRequest>());
        }
    }

    public Task<bool> SubscribeToEventsAsync(Guid agentId, IEnumerable<AgentEventType> eventTypes, CancellationToken cancellationToken = default)
    {
        try
        {
            var subscriptions = _eventSubscriptions.GetOrAdd(agentId, _ => new HashSet<AgentEventType>());
            
            lock (subscriptions)
            {
                foreach (var eventType in eventTypes)
                {
                    subscriptions.Add(eventType);
                }
            }

            _logger.LogDebug("Agent {AgentId} subscribed to {EventCount} event types", agentId, eventTypes.Count());
            return Task.FromResult(true);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to subscribe agent {AgentId} to events", agentId);
            return Task.FromResult(false);
        }
    }

    public Task<bool> UnsubscribeFromEventsAsync(Guid agentId, IEnumerable<AgentEventType> eventTypes, CancellationToken cancellationToken = default)
    {
        try
        {
            if (_eventSubscriptions.TryGetValue(agentId, out var subscriptions))
            {
                lock (subscriptions)
                {
                    foreach (var eventType in eventTypes)
                    {
                        subscriptions.Remove(eventType);
                    }
                }
            }

            _logger.LogDebug("Agent {AgentId} unsubscribed from {EventCount} event types", agentId, eventTypes.Count());
            return Task.FromResult(true);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to unsubscribe agent {AgentId} from events", agentId);
            return Task.FromResult(false);
        }
    }

    public async Task<bool> PublishEventAsync(Guid publisherAgentId, AgentEvent agentEvent, CancellationToken cancellationToken = default)
    {
        try
        {
            var eventWithDefaults = agentEvent with 
            { 
                PublisherAgentId = publisherAgentId,
                Timestamp = agentEvent.Timestamp == default ? DateTime.UtcNow : agentEvent.Timestamp
            };

            // Find subscribers
            var subscribers = _eventSubscriptions
                .Where(kvp => kvp.Value.Contains(eventWithDefaults.Type))
                .Select(kvp => kvp.Key)
                .Where(agentId => agentId != publisherAgentId)
                .ToList();

            // Send event as message to subscribers
            foreach (var subscriberId in subscribers)
            {
                var eventMessage = new AgentMessage(
                    Guid.NewGuid(),
                    publisherAgentId,
                    subscriberId,
                    $"Event: {eventWithDefaults.Description}",
                    MessageType.Status,
                    MessagePriority.Low,
                    eventWithDefaults.Data != null ? new Dictionary<string, object>(eventWithDefaults.Data) : null
                );

                await SendMessageAsync(publisherAgentId, subscriberId, eventMessage, cancellationToken);
            }

            _logger.LogDebug("Published event {EventType} from agent {PublisherAgentId} to {SubscriberCount} subscribers", 
                eventWithDefaults.Type, publisherAgentId, subscribers.Count);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to publish event from agent {PublisherAgentId}", publisherAgentId);
            return false;
        }
    }

    public Task<bool> UpdateAgentStatusAsync(Guid agentId, AgentPresenceStatus status, string? customMessage = null, CancellationToken cancellationToken = default)
    {
        try
        {
            var presence = new AgentPresence(
                agentId,
                status,
                customMessage,
                DateTime.UtcNow,
                DateTime.UtcNow
            );

            _agentPresences[agentId] = presence;

            _logger.LogDebug("Updated presence for agent {AgentId}: {Status}", agentId, status);
            return Task.FromResult(true);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to update status for agent {AgentId}", agentId);
            return Task.FromResult(false);
        }
    }

    public Task<AgentPresence?> GetAgentPresenceAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        _agentPresences.TryGetValue(agentId, out var presence);
        return Task.FromResult(presence);
    }

    public Task<IEnumerable<AgentPresence>> GetOnlineAgentsAsync(CancellationToken cancellationToken = default)
    {
        try
        {
            var onlineAgents = _agentPresences.Values
                .Where(p => p.Status == AgentPresenceStatus.Online || p.Status == AgentPresenceStatus.Busy)
                .OrderByDescending(p => p.LastSeen)
                .ToList();

            return Task.FromResult<IEnumerable<AgentPresence>>(onlineAgents);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get online agents");
            return Task.FromResult<IEnumerable<AgentPresence>>(Enumerable.Empty<AgentPresence>());
        }
    }

    public async Task<bool> DelegateTaskAsync(Guid fromAgentId, Guid toAgentId, TaskDelegation delegation, CancellationToken cancellationToken = default)
    {
        try
        {
            _delegations[delegation.Id] = delegation;

            // Send delegation as request
            var request = new AgentRequest(
                Guid.NewGuid(),
                fromAgentId,
                toAgentId,
                "TaskDelegation",
                $"Task delegation: {delegation.TaskDescription}",
                new Dictionary<string, object> { ["delegationId"] = delegation.Id.ToString() },
                (RequestPriority)(int)delegation.Priority,
                delegation.Deadline
            );

            await SendRequestAsync(fromAgentId, toAgentId, request, cancellationToken);

            _logger.LogInformation("Task delegated from agent {FromAgentId} to agent {ToAgentId}: {TaskDescription}", 
                fromAgentId, toAgentId, delegation.TaskDescription);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to delegate task from agent {FromAgentId} to agent {ToAgentId}", fromAgentId, toAgentId);
            return false;
        }
    }

    public async Task<bool> RequestCollaborationAsync(Guid initiatorAgentId, CollaborationRequest collaboration, CancellationToken cancellationToken = default)
    {
        try
        {
            _collaborations[collaboration.Id] = collaboration;

            // Send collaboration request to all requested agents
            foreach (var agentId in collaboration.RequestedAgentIds)
            {
                var request = new AgentRequest(
                    Guid.NewGuid(),
                    initiatorAgentId,
                    agentId,
                    "CollaborationRequest",
                    collaboration.Description,
                    new Dictionary<string, object> { ["collaborationId"] = collaboration.Id.ToString() },
                    RequestPriority.Medium,
                    TimeSpan.FromHours(24)
                );

                await SendRequestAsync(initiatorAgentId, agentId, request, cancellationToken);
            }

            _logger.LogInformation("Collaboration request sent from agent {InitiatorAgentId} to {AgentCount} agents: {Purpose}", 
                initiatorAgentId, collaboration.RequestedAgentIds.Count(), collaboration.Purpose);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to send collaboration request from agent {InitiatorAgentId}", initiatorAgentId);
            return false;
        }
    }

    public Task<bool> RespondToCollaborationAsync(Guid collaborationId, Guid agentId, CollaborationResponse response, CancellationToken cancellationToken = default)
    {
        try
        {
            // In a real implementation, this would be stored and processed
            _logger.LogInformation("Agent {AgentId} responded to collaboration {CollaborationId}: {Accepted}", 
                agentId, collaborationId, response.Accepted);

            return Task.FromResult(true);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to process collaboration response from agent {AgentId}", agentId);
            return Task.FromResult(false);
        }
    }

    public async Task<bool> ShareKnowledgeAsync(Guid fromAgentId, KnowledgeShare knowledge, IEnumerable<Guid>? targetAgentIds = null, CancellationToken cancellationToken = default)
    {
        try
        {
            var knowledgeWithDefaults = knowledge with 
            { 
                FromAgentId = fromAgentId,
                CreatedAt = knowledge.CreatedAt == default ? DateTime.UtcNow : knowledge.CreatedAt
            };

            // Store knowledge
            var agentKnowledge = _knowledgeBase.GetOrAdd(fromAgentId, _ => new List<KnowledgeShare>());
            lock (agentKnowledge)
            {
                agentKnowledge.Add(knowledgeWithDefaults);
                if (agentKnowledge.Count > 500) // Keep last 500 knowledge items per agent
                {
                    agentKnowledge.RemoveAt(0);
                }
            }

            // Share with target agents or broadcast
            var targets = targetAgentIds?.ToList() ?? (await _agentRepository.GetActiveAgentsAsync(cancellationToken))
                .Where(a => a.Id != fromAgentId)
                .Select(a => a.Id)
                .ToList();

            var message = new AgentMessage(
                Guid.NewGuid(),
                fromAgentId,
                Guid.Empty, // Will be set per target
                $"Knowledge shared: {knowledge.Topic}",
                MessageType.Resource,
                MessagePriority.Low,
                new Dictionary<string, object> 
                { 
                    ["knowledgeId"] = knowledge.Id.ToString(),
                    ["domain"] = knowledge.Domain,
                    ["topic"] = knowledge.Topic
                }
            );

            foreach (var targetId in targets)
            {
                var targetMessage = message with { ToAgentId = targetId };
                await SendMessageAsync(fromAgentId, targetId, targetMessage, cancellationToken);
            }

            _logger.LogInformation("Knowledge shared by agent {FromAgentId} on topic '{Topic}' to {TargetCount} agents", 
                fromAgentId, knowledge.Topic, targets.Count);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to share knowledge from agent {FromAgentId}", fromAgentId);
            return false;
        }
    }

    public Task<IEnumerable<KnowledgeShare>> QueryKnowledgeAsync(Guid agentId, KnowledgeQuery query, CancellationToken cancellationToken = default)
    {
        try
        {
            var allKnowledge = _knowledgeBase.Values.SelectMany(kb => kb);
            
            var filteredKnowledge = allKnowledge
                .Where(k => k.Domain.Equals(query.Domain, StringComparison.OrdinalIgnoreCase))
                .Where(k => query.Topic == null || k.Topic.Contains(query.Topic, StringComparison.OrdinalIgnoreCase))
                .Where(k => query.Type == null || k.Type == query.Type)
                .Where(k => query.MinConfidence == null || k.Confidence >= query.MinConfidence)
                .Where(k => query.Keywords == null || !query.Keywords.Any() || 
                           query.Keywords.Any(keyword => k.Tags.Any(tag => tag.Contains(keyword, StringComparison.OrdinalIgnoreCase))))
                .OrderByDescending(k => k.Confidence)
                .ThenByDescending(k => k.CreatedAt)
                .Take(50)
                .ToList();

            return Task.FromResult<IEnumerable<KnowledgeShare>>(filteredKnowledge);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to query knowledge for agent {AgentId}", agentId);
            return Task.FromResult<IEnumerable<KnowledgeShare>>(Enumerable.Empty<KnowledgeShare>());
        }
    }

    public async Task<bool> RequestExpertiseAsync(Guid requestingAgentId, ExpertiseRequest expertise, CancellationToken cancellationToken = default)
    {
        try
        {
            // Find agents with expertise in the domain
            var expertAgents = await _agentRepository.GetActiveAgentsAsync(cancellationToken);
            var relevantExperts = expertAgents
                .Where(a => a.Id != requestingAgentId)
                .Where(a => a.HasSpecializationIn(expertise.Domain))
                .OrderByDescending(a => a.GetSpecializationsByDomain(expertise.Domain).Max(s => s.SkillLevel * s.Confidence))
                .Take(3)
                .ToList();

            if (!relevantExperts.Any())
            {
                _logger.LogWarning("No experts found for domain '{Domain}' for agent {RequestingAgentId}", expertise.Domain, requestingAgentId);
                return false;
            }

            // Send expertise request to experts
            foreach (var expert in relevantExperts)
            {
                var request = new AgentRequest(
                    Guid.NewGuid(),
                    requestingAgentId,
                    expert.Id,
                    "ExpertiseRequest",
                    expertise.Question,
                    new Dictionary<string, object> 
                    { 
                        ["domain"] = expertise.Domain,
                        ["context"] = expertise.Context,
                        ["expertiseRequestId"] = expertise.Id.ToString()
                    },
                    expertise.Priority,
                    expertise.Timeout
                );

                await SendRequestAsync(requestingAgentId, expert.Id, request, cancellationToken);
            }

            _logger.LogInformation("Expertise request sent from agent {RequestingAgentId} to {ExpertCount} experts in domain '{Domain}'", 
                requestingAgentId, relevantExperts.Count, expertise.Domain);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to request expertise from agent {RequestingAgentId}", requestingAgentId);
            return false;
        }
    }

    public Task<CommunicationAnalytics> GetCommunicationAnalyticsAsync(Guid agentId, TimeSpan? period = null, CancellationToken cancellationToken = default)
    {
        try
        {
            var analysisPeriod = period ?? TimeSpan.FromDays(30);
            var cutoffDate = DateTime.UtcNow.Subtract(analysisPeriod);

            var agentMessages = _agentMessages.GetValueOrDefault(agentId, new List<AgentMessage>());
            var allAgentMessages = _allMessages.Values.Where(m => m.FromAgentId == agentId);

            var messagesSent = allAgentMessages.Count(m => m.Timestamp >= cutoffDate);
            var messagesReceived = agentMessages.Count(m => m.Timestamp >= cutoffDate);

            var conversations = _conversations.Values
                .Where(c => c.ParticipantIds.Contains(agentId) && c.CreatedAt >= cutoffDate);

            var conversationsStarted = conversations.Count(c => c.ParticipantIds.First() == agentId);
            var conversationsParticipated = conversations.Count();

            var requests = _requests.Values.Where(r => r.CreatedAt >= cutoffDate);
            var requestsSent = requests.Count(r => r.FromAgentId == agentId);
            var requestsReceived = requests.Count(r => r.ToAgentId == agentId);

            var knowledge = _knowledgeBase.GetValueOrDefault(agentId, new List<KnowledgeShare>());
            var knowledgeShared = knowledge.Count(k => k.CreatedAt >= cutoffDate);
            var knowledgeReceived = 0; // Would need to track received knowledge

            var topPartners = allAgentMessages
                .Where(m => m.Timestamp >= cutoffDate)
                .GroupBy(m => m.ToAgentId)
                .OrderByDescending(g => g.Count())
                .Take(5)
                .ToDictionary(g => g.Key, g => g.Count());

            var topicDistribution = new Dictionary<string, int>(); // Would need message content analysis

            return Task.FromResult(new CommunicationAnalytics(
                agentId,
                analysisPeriod,
                messagesSent,
                messagesReceived,
                conversationsStarted,
                conversationsParticipated,
                requestsSent,
                requestsReceived,
                knowledgeShared,
                knowledgeReceived,
                topPartners,
                topicDistribution
            ));
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get communication analytics for agent {AgentId}", agentId);
            throw;
        }
    }

    public Task<IEnumerable<CommunicationPattern>> AnalyzeCommunicationPatternsAsync(IEnumerable<Guid>? agentIds = null, CancellationToken cancellationToken = default)
    {
        try
        {
            var patterns = new List<CommunicationPattern>();

            // Simple pattern analysis - in production this would be more sophisticated
            var targetAgents = agentIds?.ToHashSet() ?? _agentPresences.Keys.ToHashSet();
            
            // High communication pairs
            var messageCounts = _allMessages.Values
                .Where(m => targetAgents.Contains(m.FromAgentId) && targetAgents.Contains(m.ToAgentId))
                .GroupBy(m => new { m.FromAgentId, m.ToAgentId })
                .Where(g => g.Count() > 10)
                .Select(g => new CommunicationPattern(
                    "High Communication Pair",
                    $"Agents {g.Key.FromAgentId:N} and {g.Key.ToAgentId:N} communicate frequently",
                    new[] { g.Key.FromAgentId, g.Key.ToAgentId },
                    g.Count() / 30.0, // Frequency per day over 30 days
                    new Dictionary<string, object> { ["messageCount"] = g.Count() }
                ))
                .ToList();

            patterns.AddRange(messageCounts);

            return Task.FromResult<IEnumerable<CommunicationPattern>>(patterns);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to analyze communication patterns");
            return Task.FromResult<IEnumerable<CommunicationPattern>>(Enumerable.Empty<CommunicationPattern>());
        }
    }
}