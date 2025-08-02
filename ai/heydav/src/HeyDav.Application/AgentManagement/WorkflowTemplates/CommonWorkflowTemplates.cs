using HeyDav.Application.AgentManagement.Services;
using HeyDav.Domain.AgentManagement.Enums;

namespace HeyDav.Application.AgentManagement.WorkflowTemplates;

public static class CommonWorkflowTemplates
{
    public static WorkflowDefinition CodeReviewWorkflow()
    {
        var steps = new[]
        {
            new WorkflowStep(
                "analysis",
                "Code Analysis",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Analyze the provided code for potential issues",
                    "Programming",
                    "Code Analysis",
                    new[] { "code-review", "static-analysis", "best-practices" },
                    new[] { "code-analysis" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(15),
                    7,
                    0.8
                )
            ),
            new WorkflowStep(
                "security-check",
                "Security Review",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Check code for security vulnerabilities",
                    "Security",
                    "Code Security",
                    new[] { "security", "vulnerability", "code-review" },
                    new[] { "security-analysis" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(10),
                    8,
                    0.9
                )
            ),
            new WorkflowStep(
                "performance-review",
                "Performance Analysis",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Analyze code for performance optimizations",
                    "Performance",
                    "Code Optimization",
                    new[] { "performance", "optimization", "profiling" },
                    new[] { "performance-analysis" },
                    TaskPriority.Medium,
                    TimeSpan.FromMinutes(20),
                    6,
                    0.7
                )
            ),
            new WorkflowStep(
                "consolidate-feedback",
                "Consolidate Review Results",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Consolidate all review feedback into a comprehensive report",
                    "Writing",
                    "Technical Documentation",
                    new[] { "documentation", "consolidation", "reporting" },
                    new[] { "report-generation" },
                    TaskPriority.Medium,
                    TimeSpan.FromMinutes(10)
                )
            )
        };

        var transitions = new[]
        {
            new WorkflowTransition("analysis", "security-check"),
            new WorkflowTransition("analysis", "performance-review"),
            new WorkflowTransition("security-check", "consolidate-feedback"),
            new WorkflowTransition("performance-review", "consolidate-feedback")
        };

        var settings = new WorkflowSettings(
            AllowParallelExecution: true,
            FailOnStepError: false,
            MaxExecutionTime: TimeSpan.FromHours(2),
            MaxConcurrentSteps: 3,
            EnableRetries: true,
            Priority: WorkflowPriority.High
        );

        return new WorkflowDefinition(
            "Code Review Workflow",
            "Comprehensive code review process involving multiple specialized agents",
            steps,
            transitions,
            settings,
            new Dictionary<string, object>
            {
                ["review-type"] = "comprehensive",
                ["include-security"] = true,
                ["include-performance"] = true
            }
        );
    }

    public static WorkflowDefinition ContentCreationWorkflow()
    {
        var steps = new[]
        {
            new WorkflowStep(
                "research",
                "Content Research",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Research the topic and gather relevant information",
                    "Research",
                    "Information Gathering",
                    new[] { "research", "fact-checking", "information-gathering" },
                    new[] { "research-capability" },
                    TaskPriority.Medium,
                    TimeSpan.FromMinutes(30),
                    6,
                    0.8
                )
            ),
            new WorkflowStep(
                "outline",
                "Create Content Outline",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Create a structured outline based on research",
                    "Planning",
                    "Content Planning",
                    new[] { "planning", "structure", "outline" },
                    new[] { "content-planning" },
                    TaskPriority.Medium,
                    TimeSpan.FromMinutes(15),
                    7,
                    0.8
                )
            ),
            new WorkflowStep(
                "draft",
                "Write First Draft",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Write the first draft of the content",
                    "Writing",
                    "Content Creation",
                    new[] { "writing", "content-creation", "drafting" },
                    new[] { "content-writing" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(45),
                    8,
                    0.8
                )
            ),
            new WorkflowStep(
                "review-edit",
                "Review and Edit",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Review and edit the draft for quality and accuracy",
                    "Writing",
                    "Content Editing",
                    new[] { "editing", "proofreading", "quality-check" },
                    new[] { "content-editing" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(20),
                    8,
                    0.9
                )
            ),
            new WorkflowStep(
                "final-review",
                "Final Quality Check",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Perform final quality check and approval",
                    "Quality Assurance",
                    "Content Review",
                    new[] { "quality-assurance", "final-review", "approval" },
                    new[] { "quality-review" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(10),
                    9,
                    0.9
                )
            )
        };

        var transitions = new[]
        {
            new WorkflowTransition("research", "outline"),
            new WorkflowTransition("outline", "draft"),
            new WorkflowTransition("draft", "review-edit"),
            new WorkflowTransition("review-edit", "final-review", 
                new WorkflowCondition(
                    WorkflowConditionType.DataGreaterThan, 
                    "quality-score > 0.7",
                    new Dictionary<string, object> { ["key"] = "quality-score", ["value"] = 0.7 }
                )
            ),
            new WorkflowTransition("review-edit", "draft", 
                new WorkflowCondition(
                    WorkflowConditionType.DataLessThan, 
                    "quality-score <= 0.7",
                    new Dictionary<string, object> { ["key"] = "quality-score", ["value"] = 0.7 }
                )
            )
        };

        var settings = new WorkflowSettings(
            AllowParallelExecution: false,
            FailOnStepError: true,
            MaxExecutionTime: TimeSpan.FromHours(4),
            MaxConcurrentSteps: 1,
            EnableRetries: true,
            Priority: WorkflowPriority.Medium
        );

        return new WorkflowDefinition(
            "Content Creation Workflow",
            "End-to-end content creation process from research to final approval",
            steps,
            transitions,
            settings,
            new Dictionary<string, object>
            {
                ["content-type"] = "article",
                ["target-length"] = 1500,
                ["quality-threshold"] = 0.8
            }
        );
    }

    public static WorkflowDefinition ProjectPlanningWorkflow()
    {
        var steps = new[]
        {
            new WorkflowStep(
                "requirements-analysis",
                "Analyze Requirements",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Analyze and document project requirements",
                    "Analysis",
                    "Requirements Analysis",
                    new[] { "analysis", "requirements", "documentation" },
                    new[] { "requirements-analysis" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(60),
                    8,
                    0.9
                )
            ),
            new WorkflowStep(
                "task-breakdown",
                "Break Down Tasks",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Break down the project into manageable tasks",
                    "Planning",
                    "Task Management",
                    new[] { "task-breakdown", "planning", "project-management" },
                    new[] { "task-planning" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(45),
                    7,
                    0.8
                )
            ),
            new WorkflowStep(
                "resource-planning",
                "Plan Resources",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Plan resource allocation and dependencies",
                    "Management",
                    "Resource Planning",
                    new[] { "resource-planning", "allocation", "dependencies" },
                    new[] { "resource-management" },
                    TaskPriority.Medium,
                    TimeSpan.FromMinutes(30),
                    6,
                    0.7
                )
            ),
            new WorkflowStep(
                "timeline-creation",
                "Create Timeline",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Create project timeline and milestones",
                    "Planning",
                    "Schedule Planning",
                    new[] { "scheduling", "timeline", "milestones" },
                    new[] { "schedule-planning" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(40),
                    7,
                    0.8
                )
            ),
            new WorkflowStep(
                "risk-assessment",
                "Assess Risks",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Identify and assess project risks",
                    "Analysis",
                    "Risk Assessment",
                    new[] { "risk-assessment", "analysis", "mitigation" },
                    new[] { "risk-analysis" },
                    TaskPriority.Medium,
                    TimeSpan.FromMinutes(25),
                    7,
                    0.8
                )
            ),
            new WorkflowStep(
                "plan-consolidation",
                "Consolidate Plan",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Consolidate all planning elements into comprehensive project plan",
                    "Writing",
                    "Project Documentation",
                    new[] { "documentation", "consolidation", "project-plan" },
                    new[] { "project-documentation" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(30),
                    8,
                    0.9
                )
            )
        };

        var transitions = new[]
        {
            new WorkflowTransition("requirements-analysis", "task-breakdown"),
            new WorkflowTransition("task-breakdown", "resource-planning"),
            new WorkflowTransition("task-breakdown", "timeline-creation"),
            new WorkflowTransition("resource-planning", "risk-assessment"),
            new WorkflowTransition("timeline-creation", "risk-assessment"),
            new WorkflowTransition("risk-assessment", "plan-consolidation")
        };

        var settings = new WorkflowSettings(
            AllowParallelExecution: true,
            FailOnStepError: true,
            MaxExecutionTime: TimeSpan.FromHours(6),
            MaxConcurrentSteps: 2,
            EnableRetries: true,
            Priority: WorkflowPriority.High
        );

        return new WorkflowDefinition(
            "Project Planning Workflow",
            "Comprehensive project planning workflow with multiple analysis phases",
            steps,
            transitions,
            settings,
            new Dictionary<string, object>
            {
                ["project-type"] = "software-development",
                ["planning-depth"] = "detailed",
                ["include-risk-analysis"] = true
            }
        );
    }

    public static WorkflowDefinition BugFixWorkflow()
    {
        var steps = new[]
        {
            new WorkflowStep(
                "bug-analysis",
                "Analyze Bug Report",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Analyze the bug report and reproduction steps",
                    "Analysis",
                    "Bug Analysis",
                    new[] { "bug-analysis", "debugging", "problem-solving" },
                    new[] { "bug-analysis" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(20),
                    8,
                    0.8
                )
            ),
            new WorkflowStep(
                "root-cause-analysis",
                "Find Root Cause",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Identify the root cause of the bug",
                    "Programming",
                    "Debugging",
                    new[] { "debugging", "root-cause", "investigation" },
                    new[] { "debugging" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(45),
                    8,
                    0.8
                )
            ),
            new WorkflowStep(
                "solution-design",
                "Design Solution",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Design a solution to fix the bug",
                    "Programming",
                    "Solution Design",
                    new[] { "solution-design", "architecture", "planning" },
                    new[] { "solution-design" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(30),
                    7,
                    0.8
                )
            ),
            new WorkflowStep(
                "implement-fix",
                "Implement Fix",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Implement the bug fix",
                    "Programming",
                    "Code Implementation",
                    new[] { "coding", "implementation", "bug-fix" },
                    new[] { "code-implementation" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(60),
                    8,
                    0.8
                )
            ),
            new WorkflowStep(
                "test-fix",
                "Test Bug Fix",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Test the bug fix to ensure it works correctly",
                    "Testing",
                    "Bug Testing",
                    new[] { "testing", "verification", "quality-assurance" },
                    new[] { "bug-testing" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(30),
                    8,
                    0.9
                )
            ),
            new WorkflowStep(
                "regression-test",
                "Regression Testing",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Run regression tests to ensure no new issues",
                    "Testing",
                    "Regression Testing",
                    new[] { "regression-testing", "automated-testing", "quality-assurance" },
                    new[] { "regression-testing" },
                    TaskPriority.Medium,
                    TimeSpan.FromMinutes(20),
                    7,
                    0.8
                )
            )
        };

        var transitions = new[]
        {
            new WorkflowTransition("bug-analysis", "root-cause-analysis"),
            new WorkflowTransition("root-cause-analysis", "solution-design"),
            new WorkflowTransition("solution-design", "implement-fix"),
            new WorkflowTransition("implement-fix", "test-fix"),
            new WorkflowTransition("test-fix", "regression-test",
                new WorkflowCondition(
                    WorkflowConditionType.DataEquals,
                    "fix-verified = true",
                    new Dictionary<string, object> { ["key"] = "fix-verified", ["value"] = true }
                )
            ),
            new WorkflowTransition("test-fix", "root-cause-analysis",
                new WorkflowCondition(
                    WorkflowConditionType.DataEquals,
                    "fix-verified = false",
                    new Dictionary<string, object> { ["key"] = "fix-verified", ["value"] = false }
                )
            )
        };

        var settings = new WorkflowSettings(
            AllowParallelExecution: false,
            FailOnStepError: false,
            MaxExecutionTime: TimeSpan.FromHours(8),
            MaxConcurrentSteps: 1,
            EnableRetries: true,
            Priority: WorkflowPriority.High
        );

        return new WorkflowDefinition(
            "Bug Fix Workflow",
            "Systematic bug fixing process with testing and verification",
            steps,
            transitions,
            settings,
            new Dictionary<string, object>
            {
                ["bug-severity"] = "medium",
                ["require-regression-testing"] = true,
                ["max-fix-attempts"] = 3
            }
        );
    }

    public static WorkflowDefinition DataAnalysisWorkflow()
    {
        var steps = new[]
        {
            new WorkflowStep(
                "data-collection",
                "Collect Data",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Collect and prepare data for analysis",
                    "Data",
                    "Data Collection",
                    new[] { "data-collection", "data-preparation", "etl" },
                    new[] { "data-collection" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(30),
                    7,
                    0.8
                )
            ),
            new WorkflowStep(
                "data-cleaning",
                "Clean Data",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Clean and validate the collected data",
                    "Data",
                    "Data Cleaning",
                    new[] { "data-cleaning", "validation", "preprocessing" },
                    new[] { "data-cleaning" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(45),
                    7,
                    0.8
                )
            ),
            new WorkflowStep(
                "exploratory-analysis",
                "Exploratory Analysis",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Perform exploratory data analysis",
                    "Analytics",
                    "Exploratory Analysis",
                    new[] { "exploratory-analysis", "statistics", "visualization" },
                    new[] { "data-analysis" },
                    TaskPriority.Medium,
                    TimeSpan.FromMinutes(60),
                    8,
                    0.8
                )
            ),
            new WorkflowStep(
                "statistical-analysis",
                "Statistical Analysis",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Perform detailed statistical analysis",
                    "Analytics",
                    "Statistical Analysis",
                    new[] { "statistical-analysis", "hypothesis-testing", "modeling" },
                    new[] { "statistical-analysis" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(90),
                    8,
                    0.9
                )
            ),
            new WorkflowStep(
                "visualization",
                "Create Visualizations",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Create charts and visualizations of the results",
                    "Analytics",
                    "Data Visualization",
                    new[] { "visualization", "charting", "reporting" },
                    new[] { "data-visualization" },
                    TaskPriority.Medium,
                    TimeSpan.FromMinutes(40),
                    7,
                    0.8
                )
            ),
            new WorkflowStep(
                "report-generation",
                "Generate Report",
                WorkflowStepType.AgentTask,
                new TaskRequirements(
                    "Generate comprehensive analysis report",
                    "Writing",
                    "Report Writing",
                    new[] { "report-writing", "documentation", "summary" },
                    new[] { "report-generation" },
                    TaskPriority.High,
                    TimeSpan.FromMinutes(50),
                    8,
                    0.9
                )
            )
        };

        var transitions = new[]
        {
            new WorkflowTransition("data-collection", "data-cleaning"),
            new WorkflowTransition("data-cleaning", "exploratory-analysis"),
            new WorkflowTransition("exploratory-analysis", "statistical-analysis"),
            new WorkflowTransition("exploratory-analysis", "visualization"),
            new WorkflowTransition("statistical-analysis", "visualization"),
            new WorkflowTransition("statistical-analysis", "report-generation"),
            new WorkflowTransition("visualization", "report-generation")
        };

        var settings = new WorkflowSettings(
            AllowParallelExecution: true,
            FailOnStepError: true,
            MaxExecutionTime: TimeSpan.FromHours(8),
            MaxConcurrentSteps: 2,
            EnableRetries: true,
            Priority: WorkflowPriority.Medium
        );

        return new WorkflowDefinition(
            "Data Analysis Workflow",
            "Complete data analysis workflow from collection to reporting",
            steps,
            transitions,
            settings,
            new Dictionary<string, object>
            {
                ["analysis-type"] = "comprehensive",
                ["include-visualization"] = true,
                ["output-format"] = "report"
            }
        );
    }

    public static async Task<IEnumerable<WorkflowTemplate>> GetAllTemplatesAsync()
    {
        var templates = new List<WorkflowTemplate>
        {
            new WorkflowTemplate(
                Guid.NewGuid(),
                "Code Review",
                "Comprehensive code review workflow",
                CodeReviewWorkflow(),
                "Development",
                new[] { "code-review", "quality-assurance", "development" },
                DateTime.UtcNow,
                "System",
                0
            ),
            new WorkflowTemplate(
                Guid.NewGuid(),
                "Content Creation",
                "End-to-end content creation workflow",
                ContentCreationWorkflow(),
                "Content",
                new[] { "content-creation", "writing", "publishing" },
                DateTime.UtcNow,
                "System",
                0
            ),
            new WorkflowTemplate(
                Guid.NewGuid(),
                "Project Planning",
                "Comprehensive project planning workflow",
                ProjectPlanningWorkflow(),
                "Management",
                new[] { "project-planning", "management", "analysis" },
                DateTime.UtcNow,
                "System",
                0
            ),
            new WorkflowTemplate(
                Guid.NewGuid(),
                "Bug Fix",
                "Systematic bug fixing workflow",
                BugFixWorkflow(),
                "Development",
                new[] { "bug-fix", "debugging", "testing" },
                DateTime.UtcNow,
                "System",
                0
            ),
            new WorkflowTemplate(
                Guid.NewGuid(),
                "Data Analysis",
                "Complete data analysis workflow",
                DataAnalysisWorkflow(),
                "Analytics",
                new[] { "data-analysis", "statistics", "reporting" },
                DateTime.UtcNow,
                "System",
                0
            )
        };

        return templates;
    }
}