using HeyDav.Domain.Workflows.Entities;
using HeyDav.Domain.Workflows.Enums;
using HeyDav.Domain.Workflows.ValueObjects;

namespace HeyDav.Application.Workflows.ProductivityWorkflows;

public static class ContentCreationWorkflow
{
    public static WorkflowTemplate Create()
    {
        var template = WorkflowTemplate.Create(
            "Content Creation Workflow",
            "A comprehensive content creation workflow that guides you through research, planning, writing, editing, and publishing high-quality content efficiently.",
            WorkflowCategory.ContentCreation,
            WorkflowDifficulty.Intermediate,
            TimeSpan.FromMinutes(40),
            isBuiltIn: true);

        template.SetConfigurationSchema(@"{
            ""properties"": {
                ""contentType"": { ""type"": ""string"", ""enum"": [""Blog Post"", ""Article"", ""Report"", ""Presentation"", ""Video Script"", ""Social Media"", ""Documentation""] },
                ""targetLength"": { ""type"": ""integer"", ""minimum"": 300, ""maximum"": 10000, ""default"": 1500 },
                ""audience"": { ""type"": ""string"", ""enum"": [""Beginner"", ""Intermediate"", ""Expert"", ""General"", ""Executive""] },
                ""publishingPlatform"": { ""type"": ""string"", ""default"": ""Website"" },
                ""includeSEO"": { ""type"": ""boolean"", ""default"": true }
            }
        }");

        // Step 1: Define Content Objectives
        template.AddStepTemplate(
            "Define Content Objectives & Goals",
            "Clearly define what you want to achieve with this content and who your target audience is.",
            WorkflowStepType.Input,
            1,
            true,
            @"{
                ""objectives"": [
                    ""Educate audience"",
                    ""Drive engagement"",
                    ""Generate leads"",
                    ""Build authority"",
                    ""Share insights"",
                    ""Solve problems""
                ],
                ""audience"": {
                    ""demographics"": true,
                    ""painPoints"": true,
                    ""knowledgeLevel"": true,
                    ""preferences"": true
                },
                ""successMetrics"": [""Views"", ""Engagement"", ""Shares"", ""Conversions"", ""Feedback""]
            }");

        // Step 2: Research and Information Gathering
        template.AddStepTemplate(
            "Research & Gather Information",
            "Conduct thorough research to gather credible sources, data, and insights for your content.",
            WorkflowStepType.Action,
            2,
            true,
            @"{
                ""researchMethods"": [
                    ""Literature review"",
                    ""Expert interviews"",
                    ""Data analysis"",
                    ""Case studies"",
                    ""Competitor analysis"",
                    ""Original experiments""
                ],
                ""sources"": [
                    ""Academic papers"",
                    ""Industry reports"",
                    ""Expert blogs"", 
                    ""Official statistics"",
                    ""Interviews"",
                    ""Surveys""
                ],
                ""organization"": { ""notes"": true, ""citations"": true, ""themes"": true }
            }");

        // Step 3: Create Content Outline
        template.AddStepTemplate(
            "Create Detailed Content Outline",
            "Structure your content with a logical flow that serves your objectives and audience needs.",
            WorkflowStepType.Action,
            3,
            true,
            @"{
                ""structure"": [
                    ""Hook/Introduction"",
                    ""Problem statement"",
                    ""Main sections (3-5)"",
                    ""Supporting evidence"",
                    ""Practical applications"",
                    ""Conclusion/Call to action""
                ],
                ""sections"": {
                    ""keyPoints"": true,
                    ""supportingData"": true,
                    ""examples"": true,
                    ""wordTargets"": true
                },
                ""flow"": { ""logical"": true, ""engaging"": true, ""actionable"": true }
            }");

        // Step 4: Write First Draft
        template.AddStepTemplate(
            "Write First Draft",
            "Focus on getting your ideas down on paper without worrying about perfection. Follow your outline but allow for natural evolution.",
            WorkflowStepType.Action,
            4,
            true,
            @"{
                ""writingTips"": [
                    ""Write without editing"",
                    ""Follow your outline loosely"",
                    ""Focus on clarity over elegance"",
                    ""Include placeholder for research"",
                    ""Write in your natural voice""
                ],
                ""techniques"": [
                    ""Pomodoro writing sessions"",
                    ""Stream of consciousness"",
                    ""Talk-to-text drafting"",
                    ""Section-by-section approach""
                ],
                ""environment"": { ""distractionFree"": true, ""comfortable"": true }
            }");

        // Step 5: Add Supporting Elements
        template.AddStepTemplate(
            "Add Supporting Elements",
            "Enhance your content with visuals, examples, quotes, and other elements that support your message.",
            WorkflowStepType.Action,
            5,
            true,
            @"{
                ""supportingElements"": [
                    ""Images and graphics"",
                    ""Charts and data visualizations"",
                    ""Expert quotes"",
                    ""Case studies"",
                    ""Examples and analogies"",
                    ""Statistics and data""
                ],
                ""placement"": { ""strategic"": true, ""breakUp"": true, ""reinforce"": true },
                ""creation"": { ""original"": true, ""sourced"": true, ""attributed"": true }
            }");

        // Step 6: Content Review and Self-Edit
        template.AddStepTemplate(
            "Review & Self-Edit Content",
            "Review your content for clarity, accuracy, flow, and alignment with your objectives.",
            WorkflowStepType.Review,
            6,
            true,
            @"{
                ""reviewAreas"": [
                    ""Objective alignment"",
                    ""Audience appropriateness"",
                    ""Logical flow"",
                    ""Clarity of message"",
                    ""Supporting evidence"",
                    ""Call to action strength""
                ],
                ""editingPasses"": [
                    ""Content and structure"",
                    ""Clarity and conciseness"",
                    ""Grammar and style"",
                    ""Fact-checking"",
                    ""Final polish""
                ],
                ""tools"": [""Grammar checker"", ""Readability analyzer"", ""Plagiarism checker""]
            }");

        // Step 7: Optimize for Platform
        template.AddStepTemplate(
            "Optimize for Publishing Platform",
            "Adapt your content for the specific platform where it will be published, including SEO optimization if applicable.",
            WorkflowStepType.Action,
            7,
            true,
            @"{
                ""platformOptimization"": [
                    ""Format for platform requirements"",
                    ""Add appropriate headings/sections"",
                    ""Include relevant tags/categories"",
                    ""Optimize images for platform"",
                    ""Create engaging title/headline""
                ],
                ""SEO"": [
                    ""Keyword research and integration"",
                    ""Meta description"",
                    ""Internal/external linking"",
                    ""Alt text for images"",
                    ""URL optimization""
                ],
                ""engagement"": [""Social sharing buttons"", ""Call-to-action buttons"", ""Related content links""]
            }");

        // Step 8: Final Quality Check
        template.AddStepTemplate(
            "Final Quality Check & Approval",
            "Perform a final comprehensive review to ensure the content meets all quality standards before publishing.",
            WorkflowStepType.Review,
            8,
            true,
            @"{
                ""qualityChecklist"": [
                    ""All facts verified"",
                    ""Sources properly cited"",
                    ""Grammar and spelling correct"",
                    ""Formatting consistent"",
                    ""Images properly attributed"",
                    ""Links working correctly""
                ],
                ""approval"": { ""selfReview"": true, ""peerReview"": false, ""stakeholderApproval"": false },
                ""finalChecks"": [""Mobile responsiveness"", ""Accessibility"", ""Legal compliance""]
            }");

        // Step 9: Publish and Promote
        template.AddStepTemplate(
            "Publish & Initial Promotion",
            "Publish your content and execute initial promotion activities to maximize reach and engagement.",
            WorkflowStepType.Action,
            9,
            true,
            @"{
                ""publishing"": [
                    ""Schedule optimal publish time"",
                    ""Set up analytics tracking"",
                    ""Configure notifications"",
                    ""Create backup copies""
                ],
                ""promotion"": [
                    ""Social media posts"",
                    ""Email newsletter inclusion"",
                    ""Internal sharing"",
                    ""Community posting"",
                    ""Influencer outreach""
                ],
                ""tracking"": [""Analytics setup"", ""Engagement monitoring"", ""Performance metrics""]
            }");

        // Step 10: Performance Review Setup
        template.AddStepTemplate(
            "Set Up Performance Review",
            "Establish monitoring and review processes to measure content performance and gather insights for future content.",
            WorkflowStepType.Action,
            10,
            false,
            @"{
                ""metrics"": [
                    ""Page views/impressions"",
                    ""Engagement rate"",
                    ""Time on page"",
                    ""Social shares"",
                    ""Comments/feedback"",
                    ""Conversion rate""
                ],
                ""reviewSchedule"": [""1 day"", ""1 week"", ""1 month""],
                ""insights"": { ""topPerforming"": true, ""improvements"": true, ""futureTopics"": true },
                ""iteration"": { ""updates"": true, ""repurposing"": true, ""series"": true }
            }");

        template.AddTag("content-creation");
        template.AddTag("writing");
        template.AddTag("research");
        template.AddTag("publishing");
        template.AddTag("marketing");

        return template;
    }
}

public static class LearningWorkflow
{
    public static WorkflowTemplate Create()
    {
        var template = WorkflowTemplate.Create(
            "Learning Workflow",
            "A structured learning workflow that helps you efficiently acquire new knowledge and skills through research, active learning, practice, and knowledge retention techniques.",
            WorkflowCategory.Learning,
            WorkflowDifficulty.Intermediate,
            TimeSpan.FromMinutes(35),
            isBuiltIn: true);

        template.SetConfigurationSchema(@"{
            ""properties"": {
                ""learningType"": { ""type"": ""string"", ""enum"": [""Skill Development"", ""Knowledge Acquisition"", ""Certification"", ""Hobby"", ""Professional Development""] },
                ""timeCommitment"": { ""type"": ""string"", ""enum"": [""Daily"", ""Weekly"", ""Project-based"", ""Intensive""] },
                ""learningStyle"": { ""type"": ""string"", ""enum"": [""Visual"", ""Auditory"", ""Kinesthetic"", ""Mixed""] },
                ""difficultyLevel"": { ""type"": ""string"", ""enum"": [""Beginner"", ""Intermediate"", ""Advanced""] },
                ""practicalApplication"": { ""type"": ""boolean"", ""default"": true }
            }
        }");

        // Step 1: Define Learning Objectives
        template.AddStepTemplate(
            "Define Learning Objectives & Goals",
            "Clearly articulate what you want to learn, why you want to learn it, and how you'll measure success.",
            WorkflowStepType.Input,
            1,
            true,
            @"{
                ""objectives"": {
                    ""specific"": true,
                    ""measurable"": true,
                    ""achievable"": true,
                    ""relevant"": true,
                    ""timebound"": true
                },
                ""motivation"": [
                    ""Career advancement"",
                    ""Personal interest"",
                    ""Problem solving"",
                    ""Skill improvement"",
                    ""Certification requirement""
                ],
                ""successCriteria"": [""Knowledge tests"", ""Practical application"", ""Project completion"", ""Certification""]
            }");

        // Step 2: Assess Current Knowledge
        template.AddStepTemplate(
            "Assess Current Knowledge Level",
            "Evaluate your existing knowledge and skills in the subject area to identify gaps and starting points.",
            WorkflowStepType.Review,
            2,
            true,
            @"{
                ""assessment"": [
                    ""Self-evaluation quiz"",
                    ""Skills checklist"",
                    ""Practical exercise"",
                    ""Peer assessment"",
                    ""Professional evaluation""
                ],
                ""knowledgeGaps"": { ""identify"": true, ""prioritize"": true, ""quantify"": true },
                ""strengths"": { ""leverage"": true, ""build"": true },
                ""prerequisites"": { ""check"": true, ""plan"": true }
            }");

        // Step 3: Research and Curate Resources
        template.AddStepTemplate(
            "Research & Curate Learning Resources",
            "Find and organize high-quality learning materials that match your learning style and objectives.",
            WorkflowStepType.Action,
            3,
            true,
            @"{
                ""resourceTypes"": [
                    ""Books and eBooks"",
                    ""Online courses"",
                    ""Video tutorials"",
                    ""Podcasts"",
                    ""Interactive platforms"",
                    ""Documentation"",
                    ""Mentors/Experts""
                ],
                ""evaluation"": [
                    ""Credibility of source"",
                    ""Relevance to objectives"",
                    ""Learning style match"",
                    ""Difficulty level"",
                    ""Time investment"",
                    ""Cost-benefit""
                ],
                ""organization"": { ""priority"": true, ""sequence"": true, ""accessibility"": true }
            }");

        // Step 4: Create Learning Plan
        template.AddStepTemplate(
            "Create Structured Learning Plan",
            "Design a comprehensive learning plan that breaks down your objectives into manageable learning modules and schedules.",
            WorkflowStepType.Action,
            4,
            true,
            @"{
                ""structure"": [
                    ""Learning modules/chapters"",
                    ""Time allocation per module"",
                    ""Practice exercises"",
                    ""Review sessions"",
                    ""Assessment points"",
                    ""Application projects""
                ],
                ""schedule"": {
                    ""dailyTime"": true,
                    ""weeklyGoals"": true,
                    ""milestones"": true,
                    ""flexibility"": true
                },
                ""methods"": [""Active reading"", ""Note-taking"", ""Spaced repetition"", ""Practice problems"", ""Teaching others""]
            }");

        // Step 5: Set Up Learning Environment
        template.AddStepTemplate(
            "Set Up Optimal Learning Environment",
            "Create and organize your physical and digital learning environment for maximum focus and efficiency.",
            WorkflowStepType.Action,
            5,
            true,
            @"{
                ""physicalSpace"": [
                    ""Quiet, dedicated area"",
                    ""Good lighting"",
                    ""Comfortable seating"",
                    ""Minimal distractions"",
                    ""Learning materials organized""
                ],
                ""digitalTools"": [
                    ""Note-taking apps"",
                    ""Flashcard software"",
                    ""Progress tracking"",
                    ""Calendar integration"",
                    ""Reference management""
                ],
                ""resources"": { ""quickAccess"": true, ""backup"": true, ""offline"": true }
            }");

        // Step 6: Active Learning Session
        template.AddStepTemplate(
            "Engage in Active Learning",
            "Use active learning techniques to engage deeply with the material and enhance understanding and retention.",
            WorkflowStepType.Action,
            6,
            true,
            @"{
                ""techniques"": [
                    ""Cornell note-taking"",
                    ""Mind mapping"",
                    ""Feynman technique"",
                    ""Active reading with questions"",
                    ""Summarization"",
                    ""Concept mapping""
                ],
                ""engagement"": [
                    ""Ask questions while learning"",
                    ""Connect to prior knowledge"",
                    ""Identify real-world applications"",
                    ""Challenge assumptions"",
                    ""Seek multiple perspectives""
                ],
                ""documentation"": { ""keyInsights"": true, ""questions"": true, ""connections"": true }
            }");

        // Step 7: Practice and Application
        template.AddStepTemplate(
            "Practice & Apply Knowledge",
            "Reinforce learning through hands-on practice, exercises, and real-world application of concepts.",
            WorkflowStepType.Action,
            7,
            true,
            @"{
                ""practiceTypes"": [
                    ""Guided exercises"",
                    ""Independent projects"",
                    ""Problem-solving scenarios"",
                    ""Simulations"",
                    ""Teaching others"",
                    ""Peer collaboration""
                ],
                ""application"": [
                    ""Work projects"",
                    ""Personal projects"",
                    ""Volunteer opportunities"",
                    ""Side projects"",
                    ""Open source contributions""
                ],
                ""feedback"": { ""selfAssessment"": true, ""peerReview"": true, ""expertFeedback"": false }
            }");

        // Step 8: Knowledge Consolidation
        template.AddStepTemplate(
            "Consolidate & Organize Knowledge",
            "Organize and consolidate your learning into a coherent knowledge structure for easy reference and retention.",
            WorkflowStepType.Action,
            8,
            true,
            @"{
                ""consolidation"": [
                    ""Create comprehensive notes"",
                    ""Build knowledge maps"",
                    ""Develop cheat sheets"",
                    ""Record key insights"",
                    ""Create personal reference"",
                    ""Document lessons learned""
                ],
                ""organization"": [
                    ""Hierarchical structure"",
                    ""Cross-references"",
                    ""Tagging system"",
                    ""Search functionality"",
                    ""Regular updates""
                ],
                ""retention"": [""Spaced repetition"", ""Regular review"", ""Flashcards"", ""Teaching practice""]
            }");

        // Step 9: Assessment and Validation
        template.AddStepTemplate(
            "Assess Learning & Validate Understanding",
            "Test your understanding and validate your learning through assessments, projects, or real-world applications.",
            WorkflowStepType.Review,
            9,
            true,
            @"{
                ""assessmentTypes"": [
                    ""Self-assessment quizzes"",
                    ""Practical projects"",
                    ""Peer evaluation"",
                    ""Professional assessment"",
                    ""Certification exams"",
                    ""Portfolio review""
                ],
                ""validation"": [
                    ""Apply to real problems"",
                    ""Explain to others"",
                    ""Create original content"",
                    ""Solve new challenges"",
                    ""Teach the concept""
                ],
                ""gaps"": { ""identify"": true, ""address"": true, ""relearn"": true }
            }");

        // Step 10: Reflection and Next Steps
        template.AddStepTemplate(
            "Reflect & Plan Next Steps",
            "Reflect on your learning journey and plan how to continue developing and applying your new knowledge and skills.",
            WorkflowStepType.Review,
            10,
            true,
            @"{
                ""reflection"": [
                    ""What worked well in your learning approach?"",
                    ""What challenges did you face?"",
                    ""How has your understanding evolved?"",
                    ""What surprised you?"",
                    ""How will you apply this knowledge?""
                ],
                ""nextSteps"": [
                    ""Advanced topics to explore"",
                    ""Related skills to develop"",
                    ""Application opportunities"",
                    ""Teaching/sharing opportunities"",
                    ""Continuous learning plan""
                ],
                ""maintenance"": { ""reviewSchedule"": true, ""practiceRoutine"": true, ""updatePlan"": true }
            }");

        template.AddTag("learning");
        template.AddTag("skill-development");
        template.AddTag("knowledge-management");
        template.AddTag("education");
        template.AddTag("personal-development");

        return template;
    }
}