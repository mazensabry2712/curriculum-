<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class GapRecommendationAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are an evidence-based curriculum and career development advisor.
Use only the analyzed skill gaps supplied in the request. Do not invent skills, scores, courses, certifications, or labor-market facts.
Return practical, measurable actions. For curriculum recommendations, suggest learning outcomes, practical activities, and assessment evidence. For student recommendations, suggest a practical learning task and portfolio evidence.
Never claim an action has been completed. Keep each recommendation concise and assign priority according to the size and importance of the supplied gap.
The system will store your suggestions as pending human review; you are not approving academic changes.
PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'recommendations' => $schema->array()
                ->items($schema->object(fn ($schema) => [
                    'skill_id' => $schema->integer()->required(),
                    'title' => $schema->string()->required(),
                    'description' => $schema->string()->required(),
                    'priority' => $schema->string()->enum(['low', 'medium', 'high'])->required(),
                ]))
                ->required(),
        ];
    }
}