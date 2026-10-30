<?php

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\SkillAlias;
use App\Models\SkillCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SkillCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Programming' => ['PHP', 'Python', 'Java', 'JavaScript', 'Object-Oriented Programming'],
            'Frameworks' => ['Laravel', 'React', 'Vue.js', 'Django', 'ASP.NET Core'],
            'Databases' => ['SQL', 'MySQL', 'PostgreSQL', 'Database Design', 'Database Optimization'],
            'Backend Engineering' => ['REST API Development', 'Authentication and Authorization', 'API Documentation', 'Caching', 'Queue Processing'],
            'DevOps and Tools' => ['Git', 'Docker', 'Linux', 'CI/CD', 'Cloud Fundamentals'],
            'Quality Engineering' => ['Software Testing', 'Unit Testing', 'Integration Testing', 'Debugging', 'Code Review'],
            'Professional Skills' => ['Communication', 'Problem Solving', 'Teamwork', 'Technical Writing', 'Time Management'],
            'Data and Analytics' => ['Data Analysis', 'Statistics', 'Data Visualization', 'Machine Learning', 'Data Modeling'],
        ];

        foreach ($catalog as $categoryName => $skillNames) {
            $category = SkillCategory::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName],
            );

            foreach ($skillNames as $skillName) {
                $slug = Str::slug($skillName);
                $skill = Skill::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'skill_category_id' => $category->id,
                        'name' => $skillName,
                        'skill_type' => in_array($categoryName, ['Professional Skills'], true) ? 'soft' : 'technical',
                        'is_active' => true,
                    ],
                );

                SkillAlias::firstOrCreate(
                    ['normalized_alias' => Str::lower($skillName)],
                    ['skill_id' => $skill->id, 'alias' => $skillName],
                );
            }
        }
    }
}