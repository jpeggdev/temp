using HeyDav.Domain.Common.Base;
using HeyDav.Domain.ToolIntegrations.Enums;

namespace HeyDav.Domain.ToolIntegrations.ValueObjects;

public class SyncSettings : ValueObject
{
    public bool EnableIncrementalSync { get; private set; }
    public bool EnableConflictDetection { get; private set; }
    public bool EnableChangeTracking { get; private set; }
    public bool EnableDataValidation { get; private set; }
    public bool EnableBatchProcessing { get; private set; }
    public int BatchSize { get; private set; }
    public int MaxConcurrentOperations { get; private set; }
    public TimeSpan SyncTimeout { get; private set; }
    public Dictionary<string, string> CustomSettings { get; private set; }

    public SyncSettings()
    {
        EnableIncrementalSync = true;
        EnableConflictDetection = true;
        EnableChangeTracking = true;
        EnableDataValidation = true;
        EnableBatchProcessing = true;
        BatchSize = 100;
        MaxConcurrentOperations = 5;
        SyncTimeout = TimeSpan.FromMinutes(30);
        CustomSettings = new Dictionary<string, string>();
    }

    public SyncSettings(
        bool enableIncrementalSync = true,
        bool enableConflictDetection = true,
        bool enableChangeTracking = true,
        bool enableDataValidation = true,
        bool enableBatchProcessing = true,
        int batchSize = 100,
        int maxConcurrentOperations = 5,
        TimeSpan? syncTimeout = null,
        Dictionary<string, string>? customSettings = null)
    {
        EnableIncrementalSync = enableIncrementalSync;
        EnableConflictDetection = enableConflictDetection;
        EnableChangeTracking = enableChangeTracking;
        EnableDataValidation = enableDataValidation;
        EnableBatchProcessing = enableBatchProcessing;
        BatchSize = Math.Max(1, batchSize);
        MaxConcurrentOperations = Math.Max(1, maxConcurrentOperations);
        SyncTimeout = syncTimeout ?? TimeSpan.FromMinutes(30);
        CustomSettings = customSettings ?? new Dictionary<string, string>();
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return EnableIncrementalSync;
        yield return EnableConflictDetection;
        yield return EnableChangeTracking;
        yield return EnableDataValidation;
        yield return EnableBatchProcessing;
        yield return BatchSize;
        yield return MaxConcurrentOperations;
        yield return SyncTimeout;
        yield return CustomSettings;
    }
}

public class FieldMappingConfiguration : ValueObject
{
    public Dictionary<string, string> FieldMappings { get; private set; }
    public Dictionary<string, DataTransformationType> FieldTransformations { get; private set; }
    public Dictionary<string, string> DefaultValues { get; private set; }
    public List<string> ExcludedFields { get; private set; }
    public List<string> ReadOnlyFields { get; private set; }

    public FieldMappingConfiguration()
    {
        FieldMappings = new Dictionary<string, string>();
        FieldTransformations = new Dictionary<string, DataTransformationType>();
        DefaultValues = new Dictionary<string, string>();
        ExcludedFields = new List<string>();
        ReadOnlyFields = new List<string>();
    }

    public FieldMappingConfiguration(
        Dictionary<string, string>? fieldMappings = null,
        Dictionary<string, DataTransformationType>? fieldTransformations = null,
        Dictionary<string, string>? defaultValues = null,
        List<string>? excludedFields = null,
        List<string>? readOnlyFields = null)
    {
        FieldMappings = fieldMappings ?? new Dictionary<string, string>();
        FieldTransformations = fieldTransformations ?? new Dictionary<string, DataTransformationType>();
        DefaultValues = defaultValues ?? new Dictionary<string, string>();
        ExcludedFields = excludedFields ?? new List<string>();
        ReadOnlyFields = readOnlyFields ?? new List<string>();
    }

    public string? GetMappedField(string localField)
    {
        return FieldMappings.TryGetValue(localField, out var remoteField) ? remoteField : null;
    }

    public DataTransformationType? GetTransformation(string field)
    {
        return FieldTransformations.TryGetValue(field, out var transformation) ? transformation : null;
    }

    public string? GetDefaultValue(string field)
    {
        return DefaultValues.TryGetValue(field, out var defaultValue) ? defaultValue : null;
    }

    public bool IsFieldExcluded(string field)
    {
        return ExcludedFields.Contains(field);
    }

    public bool IsFieldReadOnly(string field)
    {
        return ReadOnlyFields.Contains(field);
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return FieldMappings;
        yield return FieldTransformations;
        yield return DefaultValues;
        yield return ExcludedFields;
        yield return ReadOnlyFields;
    }
}

public class CapabilityConfiguration : ValueObject
{
    public Dictionary<string, string> Parameters { get; private set; }
    public Dictionary<string, string> Headers { get; private set; }
    public string? EndpointTemplate { get; private set; }
    public string? RequestTemplate { get; private set; }
    public string? ResponseTemplate { get; private set; }
    public List<string> RequiredParameters { get; private set; }

    public CapabilityConfiguration()
    {
        Parameters = new Dictionary<string, string>();
        Headers = new Dictionary<string, string>();
        RequiredParameters = new List<string>();
    }

    public CapabilityConfiguration(
        Dictionary<string, string>? parameters = null,
        Dictionary<string, string>? headers = null,
        string? endpointTemplate = null,
        string? requestTemplate = null,
        string? responseTemplate = null,
        List<string>? requiredParameters = null)
    {
        Parameters = parameters ?? new Dictionary<string, string>();
        Headers = headers ?? new Dictionary<string, string>();
        EndpointTemplate = endpointTemplate;
        RequestTemplate = requestTemplate;
        ResponseTemplate = responseTemplate;
        RequiredParameters = requiredParameters ?? new List<string>();
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Parameters;
        yield return Headers;
        yield return EndpointTemplate ?? string.Empty;
        yield return RequestTemplate ?? string.Empty;
        yield return ResponseTemplate ?? string.Empty;
        yield return RequiredParameters;
    }
}

public class WebhookSecuritySettings : ValueObject
{
    public bool ValidateSignature { get; private set; }
    public string SignatureHeader { get; private set; }
    public string SignatureAlgorithm { get; private set; }
    public bool ValidateTimestamp { get; private set; }
    public int TimestampToleranceSeconds { get; private set; }
    public List<string> AllowedIpRanges { get; private set; }
    public bool RequireHttps { get; private set; }

    public WebhookSecuritySettings()
    {
        ValidateSignature = true;
        SignatureHeader = "X-Hub-Signature-256";
        SignatureAlgorithm = "sha256";
        ValidateTimestamp = true;
        TimestampToleranceSeconds = 300; // 5 minutes
        AllowedIpRanges = new List<string>();
        RequireHttps = true;
    }

    public WebhookSecuritySettings(
        bool validateSignature = true,
        string signatureHeader = "X-Hub-Signature-256",
        string signatureAlgorithm = "sha256",
        bool validateTimestamp = true,
        int timestampToleranceSeconds = 300,
        List<string>? allowedIpRanges = null,
        bool requireHttps = true)
    {
        ValidateSignature = validateSignature;
        SignatureHeader = signatureHeader ?? "X-Hub-Signature-256";
        SignatureAlgorithm = signatureAlgorithm ?? "sha256";
        ValidateTimestamp = validateTimestamp;
        TimestampToleranceSeconds = Math.Max(0, timestampToleranceSeconds);
        AllowedIpRanges = allowedIpRanges ?? new List<string>();
        RequireHttps = requireHttps;
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return ValidateSignature;
        yield return SignatureHeader;
        yield return SignatureAlgorithm;
        yield return ValidateTimestamp;
        yield return TimestampToleranceSeconds;
        yield return AllowedIpRanges;
        yield return RequireHttps;
    }
}