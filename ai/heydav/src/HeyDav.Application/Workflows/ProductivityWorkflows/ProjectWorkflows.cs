using HeyDav.Domain.Workflows.Entities;
using HeyDav.Domain.Workflows.Enums;
using HeyDav.Domain.Workflows.ValueObjects;

namespace HeyDav.Application.Workflows.ProductivityWorkflows;

public static class ProjectKickoffWorkflow
{
    public static WorkflowTemplate Create()
    {
        var template = WorkflowTemplate.Create(
            "Project Kickoff Workflow",
            "A comprehensive project kickoff workflow that ensures proper planning, team alignment, and clear milestone setting for successful project execution.",
            WorkflowCategory.ProjectManagement,
            WorkflowDifficulty.Advanced,
            TimeSpan.FromMinutes(45),
            isBuiltIn: true);

        template.SetConfigurationSchema(@"{
            ""properties"": {
                ""projectType"": { ""type"": ""string"", ""enum"": [""Software"", ""Marketing"", ""Research"", ""Operations"", ""Other""] },
                ""teamSize"": { ""type"": ""integer"", ""minimum"": 1, ""maximum"": 50, ""default"": 5 },
                ""duration"": { ""type"": ""string"", ""enum"": [""1-2 weeks"", ""1 month"", ""2-3 months"", ""6+ months""] },
                ""includeRiskAssessment"": { ""type"": ""boolean"", ""default"": true },
                ""stakeholderAnalysis"": { ""type"": ""boolean"", ""default"": true }
            }
        }");

        // Step 1: Project Vision and Objectives
        template.AddStepTemplate(
            "Define Project Vision & Objectives",
            "Clearly articulate the project's purpose, vision, and specific measurable objectives.",
            WorkflowStepType.Input,
            1,
            true,
            @"{
                ""visionStatement"": { ""maxLength"": 500, ""required"": true },
                ""objectives"": { ""type"": ""SMART"", ""minCount"": 2, ""maxCount"": 5 },
                ""successMetrics"": { ""quantitative"": true, ""qualitative"": true },
                ""timeline"": { ""startDate"": true, ""endDate"": true, ""milestones"": true }
            }");

        // Step 2: Stakeholder Identification
        template.AddStepTemplate(
            "Identify & Analyze Stakeholders",
            "Map all project stakeholders, their interests, influence levels, and communication needs.",
            WorkflowStepType.Action,
            2,
            true,
            @"{
                ""stakeholderTypes"": [
                    ""Project Sponsor"",
                    ""Team Members"",
                    ""End Users"",
                    ""Department Heads"",
                    ""External Partners"",
                    ""Vendors""
                ],
                ""analysisMatrix"": {
                    ""influence"": ""High/Medium/Low"",
                    ""interest"": ""High/Medium/Low"",
                    ""attitude"": ""Champion/Supporter/Neutral/Critic/Blocker""
                },
                ""communicationPlan"": true
            }");

        // Step 3: Scope Definition
        template.AddStepTemplate(
            "Define Project Scope & Boundaries",
            "Clearly define what's included and excluded from the project to prevent scope creep.",
            WorkflowStepType.Action,
            3,
            true,
            @"{
                ""deliverables"": { ""primary"": true, ""secondary"": true, ""assumptions"": true },
                ""inclusions"": { ""detailed"": true, ""examples"": true },
                ""exclusions"": { ""explicit"": true, ""rationale"": true },
                ""constraints"": [""Budget"", ""Time"", ""Resources"", ""Technology"", ""Regulatory""],
                ""dependencies"": { ""internal"": true, ""external"": true, ""critical"": true }
            }");

        // Step 4: Team Formation and Roles
        template.AddStepTemplate(
            "Form Team & Define Roles",
            "Assemble the project team and clearly define roles, responsibilities, and authority levels.",
            WorkflowStepType.Action,
            4,
            true,
            @"{
                ""roles"": [
                    ""Project Manager"",
                    ""Technical Lead"",
                    ""Business Analyst"",
                    ""Quality Assurance"",
                    ""Subject Matter Experts""
                ],
                ""responsibilities"": { ""detailed"": true, ""accountabilities"": true },
                ""reportingStructure"": true,
                ""decisionRights"": true,
                ""skillsAssessment"": true
            }");

        // Step 5: Resource Planning
        template.AddStepTemplate(
            "Plan Resources & Budget",
            "Identify and allocate all necessary resources including human resources, technology, and budget.",
            WorkflowStepType.Action,
            5,
            true,
            @"{
                ""resourceTypes"": [
                    ""Human Resources"",
                    ""Technology/Tools"",
                    ""Equipment"",
                    ""Training"",
                    ""External Services""
                ],
                ""budgetCategories"": [""Personnel"", ""Technology"", ""Training"", ""Travel"", ""External"", ""Contingency""],
                ""allocation"": { ""byPhase"": true, ""byRole"": true, ""timeline"": true },
                ""approvals"": true
            }");

        // Step 6: Risk Assessment
        template.AddStepTemplate(
            "Conduct Risk Assessment",
            "Identify potential risks, assess their impact and probability, and develop mitigation strategies.",
            WorkflowStepType.Action,
            6,
            true,
            @"{
                ""riskCategories"": [
                    ""Technical"",
                    ""Schedule"",
                    ""Budget"",
                    ""Resource"",
                    ""External"",
                    ""Quality""
                ],
                ""assessment"": {
                    ""probability"": ""High/Medium/Low"",
                    ""impact"": ""High/Medium/Low"",
                    ""priority"": ""calculated""
                },
                ""mitigation"": { ""preventive"": true, ""contingency"": true, ""responsible"": true }
            }");

        // Step 7: Communication Plan
        template.AddStepTemplate(
            "Establish Communication Plan",
            "Define how the team and stakeholders will communicate throughout the project lifecycle.",
            WorkflowStepType.Action,
            7,
            true,
            @"{
                ""communications"": [
                    { ""type"": ""Status Updates"", ""frequency"": ""Weekly"", ""format"": ""Email/Dashboard"" },
                    { ""type"": ""Team Meetings"", ""frequency"": ""Bi-weekly"", ""format"": ""Video/In-person"" },
                    { ""type"": ""Stakeholder Reviews"", ""frequency"": ""Monthly"", ""format"": ""Presentation"" },
                    { ""type"": ""Issue Escalation"", ""frequency"": ""As needed"", ""format"": ""Direct contact"" }
                ],
                ""channels"": [""Email"", ""Slack"", ""Project management tool"", ""Wiki""],
                ""templates"": true
            }");

        // Step 8: Project Charter Creation
        template.AddStepTemplate(
            "Create Project Charter",
            "Compile all planning elements into a formal project charter document for approval and reference.",
            WorkflowStepType.Action,
            8,
            true,
            @"{
                ""sections"": [
                    ""Executive Summary"",
                    ""Project Objectives"",
                    ""Scope Statement"",
                    ""Team Structure"",
                    ""Resource Plan"",
                    ""Risk Register"",
                    ""Communication Plan"",
                    ""Success Criteria""
                ],
                ""approvalProcess"": true,
                ""distribution"": true,
                ""versionControl"": true
            }");

        // Step 9: Milestone Planning
        template.AddStepTemplate(
            "Create Milestone Plan",
            "Break down the project into key milestones with clear deliverables and success criteria.",
            WorkflowStepType.Action,
            9,
            true,
            @"{
                ""milestoneTypes"": [
                    ""Project Initiation"",
                    ""Requirements Complete"",
                    ""Design Approval"",
                    ""Development Complete"",
                    ""Testing Complete"",
                    ""Deployment"",
                    ""Project Closure""
                ],
                ""criteria"": { ""deliverables"": true, ""acceptance"": true, ""signoff"": true },
                ""dependencies"": true,
                ""reviews"": true
            }");

        // Step 10: Kickoff Meeting Planning
        template.AddStepTemplate(
            "Plan Kickoff Meeting",
            "Organize a comprehensive kickoff meeting to align the team and formally launch the project.",
            WorkflowStepType.Action,
            10,
            true,
            @"{
                ""agenda"": [
                    ""Welcome and introductions"",
                    ""Project overview and vision"",
                    ""Roles and responsibilities"",
                    ""Timeline and milestones"",
                    ""Communication protocols"",
                    ""Q&A and next steps""
                ],
                ""attendees"": { ""required"": true, ""optional"": true },
                ""materials"": [""Charter"", ""Presentations"", ""Contact list"", ""Project workspace access""],
                ""followUp"": true
            }");

        template.AddTag("project-management");
        template.AddTag("planning");
        template.AddTag("team-collaboration");
        template.AddTag("kickoff");
        template.AddTag("strategy");

        return template;
    }
}

public static class WeeklyReviewWorkflow
{
    public static WorkflowTemplate Create()
    {
        var template = WorkflowTemplate.Create(
            "Weekly Review Workflow",
            "A comprehensive weekly review process that helps you reflect on progress, adjust goals, and plan the upcoming week for optimal productivity and growth.",
            WorkflowCategory.WeeklyReview,
            WorkflowDifficulty.Intermediate,
            TimeSpan.FromMinutes(30),
            isBuiltIn: true);

        // Auto-trigger every Friday at 4 PM
        template.SetAutoTrigger(WorkflowTrigger.Scheduled("0 16 * * 5"));

        template.SetConfigurationSchema(@"{
            ""properties"": {
                ""reviewDay"": { ""type"": ""string"", ""enum"": [""Friday"", ""Sunday"", ""Saturday""], ""default"": ""Friday"" },
                ""lookAheadWeeks"": { ""type"": ""integer"", ""minimum"": 1, ""maximum"": 4, ""default"": 2 },
                ""includePersonalGoals"": { ""type"": ""boolean"", ""default"": true },
                ""includeMoodReflection"": { ""type"": ""boolean"", ""default"": true },
                ""shareWithTeam"": { ""type"": ""boolean"", ""default"": false }
            }
        }");

        // Step 1: Week in Review - Accomplishments
        template.AddStepTemplate(
            "Review Week's Accomplishments",
            "Celebrate your wins and acknowledge everything you accomplished this week, both big and small.",
            WorkflowStepType.Review,
            1,
            true,
            @"{
                ""categories"": [
                    ""Major milestones achieved"",
                    ""Goals completed"",
                    ""Tasks finished"",
                    ""Problems solved"",
                    ""Relationships built"",
                    ""Skills developed""
                ],
                ""reflection"": { ""quantitative"": true, ""qualitative"": true },
                ""celebration"": true,
                ""shareWins"": true
            }");

        // Step 2: Challenge Analysis
        template.AddStepTemplate(
            "Analyze Challenges & Obstacles",
            "Identify what didn't go as planned and understand the root causes of any setbacks or difficulties.",
            WorkflowStepType.Review,
            2,
            true,
            @"{
                ""challengeTypes"": [
                    ""Time management issues"",
                    ""Resource constraints"",
                    ""Technical difficulties"",
                    ""Communication problems"",
                    ""Energy/motivation dips"",
                    ""External dependencies""
                ],
                ""rootCauseAnalysis"": true,
                ""patternRecognition"": true,
                ""lessonsLearned"": true
            }");

        // Step 3: Goal Progress Assessment
        template.AddStepTemplate(
            "Assess Goal Progress",
            "Evaluate progress toward your short-term and long-term goals and adjust targets if needed.",
            WorkflowStepType.Review,
            3,
            true,
            @"{
                ""goalTypes"": [
                    ""Weekly goals"",
                    ""Monthly goals"",
                    ""Quarterly goals"",
                    ""Annual goals"",
                    ""Personal development"",
                    ""Professional growth""
                ],
                ""metrics"": { ""completion"": true, ""quality"": true, ""timeline"": true },
                ""adjustments"": { ""targets"": true, ""timeline"": true, ""approach"": true }
            }");

        // Step 4: Habit and Routine Review
        template.AddStepTemplate(
            "Review Habits & Routines",
            "Analyze the effectiveness of your habits and routines in supporting your productivity and well-being.",
            WorkflowStepType.Review,
            4,
            true,
            @"{
                ""habitCategories"": [
                    ""Morning routine"",
                    ""Work habits"",
                    ""Health and wellness"",
                    ""Learning and development"",
                    ""Evening routine""
                ],
                ""effectiveness"": { ""scale"": ""1-10"", ""qualitative"": true },
                ""consistency"": { ""percentage"": true, ""patterns"": true },
                ""optimization"": true
            }");

        // Step 5: Energy and Mood Patterns
        template.AddStepTemplate(
            "Analyze Energy & Mood Patterns",
            "Reflect on your energy levels and mood throughout the week to optimize future scheduling and self-care.",
            WorkflowStepType.Review,
            5,
            false,
            @"{
                ""energyTracking"": {
                    ""dailyPatterns"": true,
                    ""peakTimes"": true,
                    ""lowPoints"": true,
                    ""correlations"": true
                },
                ""moodFactors"": [
                    ""Work satisfaction"",
                    ""Stress levels"",
                    ""Work-life balance"",
                    ""Social connections"",
                    ""Physical health""
                ],
                ""insights"": true
            }");

        // Step 6: Relationship and Communication Review
        template.AddStepTemplate(
            "Review Relationships & Communication",
            "Reflect on your professional and personal relationships and communication effectiveness this week.",
            WorkflowStepType.Review,
            6,
            false,
            @"{
                ""relationships"": [
                    ""Team collaboration"",
                    ""Manager relationship"",
                    ""Client interactions"",
                    ""Peer connections"",
                    ""Personal relationships""
                ],
                ""communication"": [
                    ""Clarity of messages"",
                    ""Response timeliness"",
                    ""Active listening"",
                    ""Conflict resolution"",
                    ""Feedback given/received""
                ],
                ""improvements"": true
            }");

        // Step 7: Learning and Growth Reflection
        template.AddStepTemplate(
            "Reflect on Learning & Growth",
            "Identify what you learned this week and how you've grown both professionally and personally.",
            WorkflowStepType.Review,
            7,
            true,
            @"{
                ""learningTypes"": [
                    ""New skills acquired"",
                    ""Knowledge gained"",
                    ""Insights discovered"",
                    ""Mistakes learned from"",
                    ""Feedback received""
                ],
                ""growthAreas"": [
                    ""Technical skills"",
                    ""Leadership abilities"",
                    ""Communication skills"",
                    ""Problem-solving"",
                    ""Emotional intelligence""
                ],
                ""application"": true
            }");

        // Step 8: Next Week Planning
        template.AddStepTemplate(
            "Plan Next Week's Priorities",
            "Set clear priorities and intentions for the upcoming week based on your review insights.",
            WorkflowStepType.Action,
            8,
            true,
            @"{
                ""priorityTypes"": [
                    ""Must-do tasks"",
                    ""Goal advancement"",
                    ""Relationship building"",
                    ""Learning activities"",
                    ""Self-care""
                ],
                ""scheduling"": { ""timeBlocking"": true, ""energyAlignment"": true },
                ""preparation"": true,
                ""contingencies"": true
            }");

        // Step 9: System and Process Optimization
        template.AddStepTemplate(
            "Optimize Systems & Processes",
            "Identify improvements to your tools, systems, and processes based on this week's experience.",
            WorkflowStepType.Action,
            9,
            false,
            @"{
                ""systemsReview"": [
                    ""Task management tools"",
                    ""Calendar organization"",
                    ""File management"",
                    ""Communication tools"",
                    ""Automation opportunities""
                ],
                ""processImprovements"": [
                    ""Workflow optimizations"",
                    ""Decision-making"",
                    ""Problem-solving approaches"",
                    ""Meeting effectiveness""
                ],
                ""implementation"": true
            }");

        // Step 10: Commitment and Accountability
        template.AddStepTemplate(
            "Set Weekly Commitment",
            "Make a clear commitment for the upcoming week and set up accountability measures.",
            WorkflowStepType.Action,
            10,
            true,
            @"{
                ""commitments"": [
                    ""Top 3 priorities"",
                    ""Personal development focus"",
                    ""Relationship investment"",
                    ""Self-care practices"",
                    ""Boundary maintenance""
                ],
                ""accountability"": [
                    ""Check-in schedule"",
                    ""Progress markers"",
                    ""Support systems"",
                    ""Review triggers""
                ],
                ""motivation"": true
            }");

        template.AddTag("weekly-review");
        template.AddTag("reflection");
        template.AddTag("planning");
        template.AddTag("continuous-improvement");
        template.AddTag("goal-tracking");

        return template;
    }
}