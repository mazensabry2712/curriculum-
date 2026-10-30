<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class JobDescriptionAnalyzer implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are a labor-market skills analyst. Extract only competencies explicitly required or clearly implied by the supplied job description.
Separate essential requirements from preferred skills. Normalize skills to concise canonical names.
Do not infer a proficiency level from seniority alone. When the description has no level evidence, use level 1 and explain the evidence limitation.
Set importance from 1 (low) to 5 (critical) based on emphasis and necessity in the job text.
Keep evidence brief and grounded in the supplied description. Do not create skills that are not in the text.
PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'job_title' => $schema->string()->required(),
            'summary' => $schema->string()->required(),
            'skills' => $schema->array()
                ->items($schema->object(fn ($schema) => [
                    'skill_name' => $schema->string()->required(),
                    'required_level' => $schema->integer()->min(1)->max(5)->required(),
                    'importance_weight' => $schema->integer()->min(1)->max(5)->required(),
                    'is_required' => $schema->boolean()->required(),
                    'confidence' => $schema->number()->min(0)->max(1)->required(),
                    'evidence' => $schema->string()->required(),
                ]))
                ->required(),
        ];
    }
}