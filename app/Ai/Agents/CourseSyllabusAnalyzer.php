<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class CourseSyllabusAnalyzer implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are an academic curriculum analyst. Analyze the attached course syllabus only.
Extract explicit learning outcomes and skills supported by evidence in the document.
Do not invent technologies, outcomes, proficiency levels, or page references.
Use a 1-5 suggested level only when the syllabus provides enough evidence:
1 awareness, 2 understanding, 3 guided application, 4 independent application, 5 advanced independent practice.
A suggested level is a proposal and must not be treated as an approved curriculum rating.
Keep evidence short and quote or closely paraphrase the relevant syllabus wording.
Return skills as atomic competencies. If an item is not a skill, do not include it.
Use English canonical skill names where possible, but preserve the original wording in evidence.
PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'course_summary' => $schema->string()->required(),
            'learning_outcomes' => $schema->array()
                ->items($schema->object(fn ($schema) => [
                    'code' => $schema->string()->required(),
                    'description' => $schema->string()->required(),
                    'evidence' => $schema->string()->required(),
                    'page_number' => $schema->integer()->min(1)->required(),
                ]))
                ->required(),
            'skills' => $schema->array()
                ->items($schema->object(fn ($schema) => [
                    'skill_name' => $schema->string()->required(),
                    'category' => $schema->string()->required(),
                    'suggested_level' => $schema->integer()->min(1)->max(5)->required(),
                    'confidence' => $schema->number()->min(0)->max(1)->required(),
                    'evidence' => $schema->string()->required(),
                    'page_number' => $schema->integer()->min(1)->required(),
                ]))
                ->required(),
        ];
    }
}