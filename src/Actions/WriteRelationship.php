<?php

namespace FumeApp\ModelTyper\Actions;

use FumeApp\ModelTyper\Traits\ClassBaseName;
use Illuminate\Support\Str;

class WriteRelationship
{
    use ClassBaseName;

    /**
     * Write the relationship to the output.
     *
     * @param  array{name: string, type: string, related:string}  $relation
     * @return array{type: string, name: string}|string
     */
    public function __invoke(array $relation, string $indent = '', bool $jsonOutput = false, bool $optionalRelation = false, bool $plurals = false): array|string
    {
        $name = Str::snake($relation['name']);
        $relatedModel = $this->getClassName($relation['related']);
        $optional = $optionalRelation ? '?' : '';

        $relationType = match ($relation['type']) {
            'BelongsToMany', 'HasMany', 'HasManyThrough', 'MorphToMany', 'MorphMany', 'MorphedByMany' => $plurals === true ? $this->buildName(Str::plural($relatedModel)) : ($this->buildName(Str::singular($relatedModel)) . '[]'),
            'BelongsTo', 'HasOne', 'HasOneThrough', 'MorphOne', 'MorphTo' => $this->buildName(Str::singular($relatedModel)),
            default => $this->buildName($relatedModel),
        };

        if (in_array($relation['type'], config('modeltyper.custom_relationships.singular', []))) {
            $relationType = $this->buildName(Str::singular($relation['type']));
        }

        if (in_array($relation['type'], config('modeltyper.custom_relationships.plural', []))) {
            $relationType = $this->buildName(Str::singular($relation['type']));
        }

        if ($jsonOutput) {
            return [
                'name' => $name,
                'type' => $relationType,
            ];
        }

        return "{$indent}  {$name}{$optional}: {$relationType}\n";
    }

    private function buildName($str){
        return config('modeltyper.prefixes.model')."$str".config('modeltyper.suffixes.model');
    }
}
