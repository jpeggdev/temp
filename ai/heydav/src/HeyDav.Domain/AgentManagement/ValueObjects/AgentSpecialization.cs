using HeyDav.Domain.Common.Base;

namespace HeyDav.Domain.AgentManagement.ValueObjects;

public class AgentSpecialization : ValueObject
{
    public string Domain { get; private set; }
    public string Subdomain { get; private set; }
    public int SkillLevel { get; private set; } // 1-10 scale
    public double Confidence { get; private set; } // 0.0-1.0
    public List<string> Keywords { get; private set; }
    public DateTime AcquiredAt { get; private set; }
    public DateTime? LastUsedAt { get; private set; }
    public int UsageCount { get; private set; }

    private AgentSpecialization()
    {
        Domain = string.Empty;
        Subdomain = string.Empty;
        Keywords = new List<string>();
    }

    private AgentSpecialization(
        string domain,
        string subdomain,
        int skillLevel,
        double confidence,
        List<string> keywords)
    {
        Domain = domain;
        Subdomain = subdomain;
        SkillLevel = skillLevel;
        Confidence = confidence;
        Keywords = keywords ?? new List<string>();
        AcquiredAt = DateTime.UtcNow;
        UsageCount = 0;
    }

    public static AgentSpecialization Create(
        string domain,
        string subdomain,
        int skillLevel = 5,
        double confidence = 0.5,
        List<string>? keywords = null)
    {
        if (string.IsNullOrWhiteSpace(domain))
            throw new ArgumentException("Domain cannot be empty", nameof(domain));

        if (string.IsNullOrWhiteSpace(subdomain))
            throw new ArgumentException("Subdomain cannot be empty", nameof(subdomain));

        if (skillLevel < 1 || skillLevel > 10)
            throw new ArgumentException("Skill level must be between 1 and 10", nameof(skillLevel));

        if (confidence < 0.0 || confidence > 1.0)
            throw new ArgumentException("Confidence must be between 0.0 and 1.0", nameof(confidence));

        return new AgentSpecialization(domain, subdomain, skillLevel, confidence, keywords ?? new List<string>());
    }

    public AgentSpecialization UpdateSkillLevel(int newSkillLevel)
    {
        if (newSkillLevel < 1 || newSkillLevel > 10)
            throw new ArgumentException("Skill level must be between 1 and 10", nameof(newSkillLevel));

        return new AgentSpecialization(Domain, Subdomain, newSkillLevel, Confidence, Keywords)
        {
            AcquiredAt = AcquiredAt,
            LastUsedAt = LastUsedAt,
            UsageCount = UsageCount
        };
    }

    public AgentSpecialization UpdateConfidence(double newConfidence)
    {
        if (newConfidence < 0.0 || newConfidence > 1.0)
            throw new ArgumentException("Confidence must be between 0.0 and 1.0", nameof(newConfidence));

        return new AgentSpecialization(Domain, Subdomain, SkillLevel, newConfidence, Keywords)
        {
            AcquiredAt = AcquiredAt,
            LastUsedAt = LastUsedAt,
            UsageCount = UsageCount
        };
    }

    public AgentSpecialization RecordUsage()
    {
        return new AgentSpecialization(Domain, Subdomain, SkillLevel, Confidence, Keywords)
        {
            AcquiredAt = AcquiredAt,
            LastUsedAt = DateTime.UtcNow,
            UsageCount = UsageCount + 1
        };
    }

    public AgentSpecialization AddKeywords(params string[] newKeywords)
    {
        var updatedKeywords = new List<string>(Keywords);
        foreach (var keyword in newKeywords.Where(k => !string.IsNullOrWhiteSpace(k)))
        {
            if (!updatedKeywords.Contains(keyword, StringComparer.OrdinalIgnoreCase))
            {
                updatedKeywords.Add(keyword.ToLowerInvariant());
            }
        }

        return new AgentSpecialization(Domain, Subdomain, SkillLevel, Confidence, updatedKeywords)
        {
            AcquiredAt = AcquiredAt,
            LastUsedAt = LastUsedAt,
            UsageCount = UsageCount
        };
    }

    public bool MatchesKeywords(IEnumerable<string> searchKeywords)
    {
        if (!searchKeywords.Any()) return true;

        return searchKeywords.Any(searchKeyword =>
            Keywords.Any(keyword =>
                keyword.Contains(searchKeyword, StringComparison.OrdinalIgnoreCase) ||
                searchKeyword.Contains(keyword, StringComparison.OrdinalIgnoreCase)));
    }

    public double CalculateRelevanceScore(string domain, string? subdomain = null, IEnumerable<string>? keywords = null)
    {
        double score = 0.0;

        // Domain match
        if (Domain.Equals(domain, StringComparison.OrdinalIgnoreCase))
        {
            score += 40.0;
        }
        else if (Domain.Contains(domain, StringComparison.OrdinalIgnoreCase) ||
                 domain.Contains(Domain, StringComparison.OrdinalIgnoreCase))
        {
            score += 20.0;
        }

        // Subdomain match
        if (!string.IsNullOrWhiteSpace(subdomain))
        {
            if (Subdomain.Equals(subdomain, StringComparison.OrdinalIgnoreCase))
            {
                score += 30.0;
            }
            else if (Subdomain.Contains(subdomain, StringComparison.OrdinalIgnoreCase) ||
                     subdomain.Contains(Subdomain, StringComparison.OrdinalIgnoreCase))
            {
                score += 15.0;
            }
        }

        // Keyword match
        if (keywords != null && keywords.Any())
        {
            var keywordList = keywords.ToList();
            var matchedKeywords = keywordList.Count(k => MatchesKeywords(new[] { k }));
            score += (matchedKeywords / (double)keywordList.Count) * 20.0;
        }

        // Skill level and confidence bonus
        score += (SkillLevel / 10.0) * 5.0;
        score += Confidence * 5.0;

        return Math.Min(100.0, score);
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Domain;
        yield return Subdomain;
        yield return SkillLevel;
        yield return Confidence;
        foreach (var keyword in Keywords.OrderBy(k => k))
        {
            yield return keyword;
        }
    }

    public override string ToString()
    {
        return $"{Domain}/{Subdomain} (Level {SkillLevel}, Confidence {Confidence:P})";
    }

    // Common specializations factory methods
    public static class CommonSpecializations
    {
        public static AgentSpecialization CSharpDevelopment() =>
            Create("Programming", "C# Development", 8, 0.9, new List<string>
            {
                "c#", "dotnet", ".net", "csharp", "visual studio", "entity framework", "asp.net", "blazor", "linq"
            });

        public static AgentSpecialization WebDevelopment() =>
            Create("Programming", "Web Development", 7, 0.8, new List<string>
            {
                "html", "css", "javascript", "typescript", "react", "angular", "vue", "nodejs", "web api"
            });

        public static AgentSpecialization DatabaseDesign() =>
            Create("Data", "Database Design", 7, 0.8, new List<string>
            {
                "sql", "database", "mysql", "postgresql", "sqlite", "mongodb", "entity framework", "migrations"
            });

        public static AgentSpecialization TechnicalWriting() =>
            Create("Writing", "Technical Documentation", 8, 0.9, new List<string>
            {
                "documentation", "technical writing", "api docs", "user manual", "readme", "markdown"
            });

        public static AgentSpecialization CodeReview() =>
            Create("Quality Assurance", "Code Review", 8, 0.85, new List<string>
            {
                "code review", "static analysis", "best practices", "code quality", "refactoring", "security"
            });

        public static AgentSpecialization Testing() =>
            Create("Quality Assurance", "Software Testing", 7, 0.8, new List<string>
            {
                "unit testing", "integration testing", "test automation", "xunit", "nunit", "moq", "test driven development"
            });

        public static AgentSpecialization ProjectPlanning() =>
            Create("Management", "Project Planning", 6, 0.7, new List<string>
            {
                "project management", "agile", "scrum", "planning", "estimation", "roadmap", "milestones"
            });

        public static AgentSpecialization DataAnalysis() =>
            Create("Analytics", "Data Analysis", 7, 0.8, new List<string>
            {
                "data analysis", "statistics", "reporting", "visualization", "metrics", "kpi", "dashboard"
            });

        public static AgentSpecialization ResearchAndInvestigation() =>
            Create("Research", "Information Gathering", 8, 0.85, new List<string>
            {
                "research", "investigation", "fact checking", "data gathering", "analysis", "synthesis"
            });

        public static AgentSpecialization ProcessAutomation() =>
            Create("Automation", "Workflow Automation", 7, 0.8, new List<string>
            {
                "automation", "workflow", "scripting", "batch processing", "task scheduling", "optimization"
            });
    }
}