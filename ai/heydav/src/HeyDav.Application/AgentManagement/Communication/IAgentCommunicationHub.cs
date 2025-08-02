namespace HeyDav.Application.AgentManagement.Communication;

public interface IAgentCommunicationHub
{
    // Message Sending
    Task<bool> SendMessageAsync(Guid fromAgentId, Guid toAgentId, AgentMessage message, CancellationToken cancellationToken = default);
    Task<bool> BroadcastMessageAsync(Guid fromAgentId, AgentMessage message, IEnumerable<Guid>? targetAgentIds = null, CancellationToken cancellationToken = default);
    Task<bool> SendNotificationAsync(Guid agentId, AgentNotification notification, CancellationToken cancellationToken = default);

    // Message Receiving
    Task<IEnumerable<AgentMessage>> GetMessagesAsync(Guid agentId, bool markAsRead = true, CancellationToken cancellationToken = default);
    Task<IEnumerable<AgentMessage>> GetUnreadMessagesAsync(Guid agentId, CancellationToken cancellationToken = default);
    Task<AgentMessage?> GetMessageAsync(Guid messageId, CancellationToken cancellationToken = default);

    // Conversation Management
    Task<Guid> StartConversationAsync(string topic, IEnumerable<Guid> participantIds, ConversationType type = ConversationType.Collaboration, CancellationToken cancellationToken = default);
    Task<bool> JoinConversationAsync(Guid conversationId, Guid agentId, CancellationToken cancellationToken = default);
    Task<bool> LeaveConversationAsync(Guid conversationId, Guid agentId, CancellationToken cancellationToken = default);
    Task<Conversation?> GetConversationAsync(Guid conversationId, CancellationToken cancellationToken = default);
    Task<IEnumerable<Conversation>> GetAgentConversationsAsync(Guid agentId, CancellationToken cancellationToken = default);

    // Request-Response Pattern
    Task<Guid> SendRequestAsync(Guid fromAgentId, Guid toAgentId, AgentRequest request, CancellationToken cancellationToken = default);
    Task<bool> SendResponseAsync(Guid requestId, AgentResponse response, CancellationToken cancellationToken = default);
    Task<AgentResponse?> WaitForResponseAsync(Guid requestId, TimeSpan timeout, CancellationToken cancellationToken = default);
    Task<IEnumerable<AgentRequest>> GetPendingRequestsAsync(Guid agentId, CancellationToken cancellationToken = default);

    // Subscription and Events
    Task<bool> SubscribeToEventsAsync(Guid agentId, IEnumerable<AgentEventType> eventTypes, CancellationToken cancellationToken = default);
    Task<bool> UnsubscribeFromEventsAsync(Guid agentId, IEnumerable<AgentEventType> eventTypes, CancellationToken cancellationToken = default);
    Task<bool> PublishEventAsync(Guid publisherAgentId, AgentEvent agentEvent, CancellationToken cancellationToken = default);

    // Presence and Status
    Task<bool> UpdateAgentStatusAsync(Guid agentId, AgentPresenceStatus status, string? customMessage = null, CancellationToken cancellationToken = default);
    Task<AgentPresence?> GetAgentPresenceAsync(Guid agentId, CancellationToken cancellationToken = default);
    Task<IEnumerable<AgentPresence>> GetOnlineAgentsAsync(CancellationToken cancellationToken = default);

    // Delegation and Coordination
    Task<bool> DelegateTaskAsync(Guid fromAgentId, Guid toAgentId, TaskDelegation delegation, CancellationToken cancellationToken = default);
    Task<bool> RequestCollaborationAsync(Guid initiatorAgentId, CollaborationRequest collaboration, CancellationToken cancellationToken = default);
    Task<bool> RespondToCollaborationAsync(Guid collaborationId, Guid agentId, CollaborationResponse response, CancellationToken cancellationToken = default);

    // Knowledge Sharing
    Task<bool> ShareKnowledgeAsync(Guid fromAgentId, KnowledgeShare knowledge, IEnumerable<Guid>? targetAgentIds = null, CancellationToken cancellationToken = default);
    Task<IEnumerable<KnowledgeShare>> QueryKnowledgeAsync(Guid agentId, KnowledgeQuery query, CancellationToken cancellationToken = default);
    Task<bool> RequestExpertiseAsync(Guid requestingAgentId, ExpertiseRequest expertise, CancellationToken cancellationToken = default);

    // Communication Analytics
    Task<CommunicationAnalytics> GetCommunicationAnalyticsAsync(Guid agentId, TimeSpan? period = null, CancellationToken cancellationToken = default);
    Task<IEnumerable<CommunicationPattern>> AnalyzeCommunicationPatternsAsync(IEnumerable<Guid>? agentIds = null, CancellationToken cancellationToken = default);
}

public record AgentMessage(
    Guid Id,
    Guid FromAgentId,
    Guid ToAgentId,
    string Content,
    MessageType Type,
    MessagePriority Priority,
    Dictionary<string, object>? Attachments = null,
    Guid? ConversationId = null,
    Guid? ReplyToMessageId = null,
    DateTime Timestamp = default,
    bool IsRead = false);

public record AgentNotification(
    Guid Id,
    string Title,
    string Content,
    NotificationType Type,
    NotificationPriority Priority,
    Dictionary<string, object>? Data = null,
    DateTime ExpiresAt = default,
    DateTime Timestamp = default);

public record Conversation(
    Guid Id,
    string Topic,
    ConversationType Type,
    IEnumerable<Guid> ParticipantIds,
    DateTime CreatedAt,
    DateTime LastActivity,
    ConversationStatus Status,
    Dictionary<string, object>? Metadata = null);

public record AgentRequest(
    Guid Id,
    Guid FromAgentId,
    Guid ToAgentId,
    string RequestType,
    string Description,
    Dictionary<string, object> Parameters,
    RequestPriority Priority,
    TimeSpan? Timeout = null,
    DateTime CreatedAt = default);

public record AgentResponse(
    Guid Id,
    Guid RequestId,
    Guid FromAgentId,
    bool Success,
    Dictionary<string, object>? Data = null,
    string? ErrorMessage = null,
    DateTime CreatedAt = default);

public record AgentEvent(
    Guid Id,
    Guid PublisherAgentId,
    AgentEventType Type,
    string Description,
    Dictionary<string, object>? Data = null,
    DateTime Timestamp = default);

public record AgentPresence(
    Guid AgentId,
    AgentPresenceStatus Status,
    string? CustomMessage,
    DateTime LastSeen,
    DateTime StatusUpdatedAt);

public record TaskDelegation(
    Guid Id,
    Guid TaskId,
    string TaskDescription,
    string Reason,
    Dictionary<string, object>? Context = null,
    TimeSpan? Deadline = null,
    DelegationPriority Priority = DelegationPriority.Medium);

public record CollaborationRequest(
    Guid Id,
    string Purpose,
    string Description,
    IEnumerable<Guid> RequestedAgentIds,
    CollaborationType Type,
    TimeSpan? Duration = null,
    Dictionary<string, object>? Requirements = null);

public record CollaborationResponse(
    Guid CollaborationId,
    bool Accepted,
    string? Reason = null,
    TimeSpan? AvailableDuration = null,
    Dictionary<string, object>? Conditions = null);

public record KnowledgeShare(
    Guid Id,
    Guid FromAgentId,
    string Domain,
    string Topic,
    string Content,
    KnowledgeType Type,
    double Confidence,
    IEnumerable<string> Tags,
    DateTime CreatedAt = default);

public record KnowledgeQuery(
    string Domain,
    string? Topic = null,
    IEnumerable<string>? Keywords = null,
    KnowledgeType? Type = null,
    double? MinConfidence = null);

public record ExpertiseRequest(
    Guid Id,
    Guid RequestingAgentId,
    string Domain,
    string Question,
    string Context,
    RequestPriority Priority,
    TimeSpan? Timeout = null);

public record CommunicationAnalytics(
    Guid AgentId,
    TimeSpan Period,
    int MessagesSent,
    int MessagesReceived,
    int ConversationsStarted,
    int ConversationsParticipated,
    int RequestsSent,
    int RequestsReceived,
    int KnowledgeShared,
    int KnowledgeReceived,
    Dictionary<Guid, int> TopCommunicationPartners,
    Dictionary<string, int> TopicDistribution);

public record CommunicationPattern(
    string PatternType,
    string Description,
    IEnumerable<Guid> InvolvedAgents,
    double Frequency,
    Dictionary<string, object>? Metrics = null);

public enum MessageType
{
    Text,
    Data,
    Command,
    Question,
    Answer,
    Status,
    Alert,
    Resource
}

public enum MessagePriority
{
    Low = 1,
    Medium = 2,
    High = 3,
    Critical = 4
}

public enum NotificationType
{
    Info,
    Warning,
    Error,
    Success,
    TaskUpdate,
    SystemAlert,
    Reminder
}

public enum NotificationPriority
{
    Low = 1,
    Medium = 2,
    High = 3,
    Critical = 4
}

public enum ConversationType
{
    Collaboration,
    Consultation,
    Planning,
    Review,
    Emergency,
    Training,
    Social
}

public enum ConversationStatus
{
    Active,
    Paused,
    Completed,
    Archived
}

public enum RequestPriority
{
    Low = 1,
    Medium = 2,
    High = 3,
    Urgent = 4
}

public enum AgentEventType
{
    TaskStarted,
    TaskCompleted,
    TaskFailed,
    StatusChanged,
    CapabilityAdded,
    SpecializationUpdated,
    ErrorOccurred,
    PerformanceAlert,
    CollaborationRequest,
    KnowledgeUpdate
}

public enum AgentPresenceStatus
{
    Online,
    Busy,
    Away,
    Offline,
    InMaintenance,
    Error
}

public enum DelegationPriority
{
    Low = 1,
    Medium = 2,
    High = 3,
    Critical = 4
}

public enum CollaborationType
{
    TaskCollaboration,
    KnowledgeSharing,
    PeerReview,
    Consultation,
    Training,
    ProblemSolving
}

public enum KnowledgeType
{
    Fact,
    Procedure,
    BestPractice,
    LessonLearned,
    Pattern,
    Solution,
    Resource,
    Insight
}