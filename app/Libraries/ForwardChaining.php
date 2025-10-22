<?php

namespace App\Libraries;

class ForwardChaining
{
    /**
     * @param array<string, mixed> $facts
     * @param list<array<string, mixed>> $rules
     *
     * @return array{facts: array<string, mixed>, fired_rules: list<mixed>, recommendations: list<mixed>}
     */
    public function run(array $facts, array $rules): array
    {
        $knownFacts = $facts;
        $firedRules = [];
        $recommendations = [];
        $hasChanges = true;

        while ($hasChanges) {
            $hasChanges = false;

            foreach ($rules as $rule) {
                $ruleId = $rule['id'] ?? $rule['rule_id'] ?? null;

                if ($ruleId !== null && in_array($ruleId, $firedRules, true)) {
                    continue;
                }

                $conditions = $rule['when'] ?? [];
                if (! is_array($conditions) || $conditions === []) {
                    continue;
                }

                if (! $this->conditionsMatch($knownFacts, $conditions)) {
                    continue;
                }

                $actions = $rule['then'] ?? [];
                if (! is_array($actions) || $actions === []) {
                    continue;
                }

                $ruleTriggered = false;

                foreach ($actions as $action) {
                    if (! is_array($action)) {
                        continue;
                    }

                    if (array_key_exists('add', $action)) {
                        $target = $action['add'];
                        $value = $action['value'] ?? true;

                        if (! array_key_exists($target, $knownFacts) || $knownFacts[$target] !== $value) {
                            $knownFacts[$target] = $value;
                            $hasChanges = true;
                        }

                        $ruleTriggered = true;
                    } elseif (array_key_exists('set', $action)) {
                        $target = $action['set'];
                        $value = $action['value'] ?? null;

                        if (! array_key_exists($target, $knownFacts) || $knownFacts[$target] !== $value) {
                            $knownFacts[$target] = $value;
                            $hasChanges = true;
                        }

                        $ruleTriggered = true;
                    }

                    if (array_key_exists('recommendation', $action)) {
                        $recommendations[] = $action['recommendation'];
                        $ruleTriggered = true;
                    }
                }

                if ($ruleTriggered) {
                    if ($ruleId !== null) {
                        $firedRules[] = $ruleId;
                    }
                }
            }
        }

        return [
            'facts'            => $knownFacts,
            'fired_rules'      => array_values(array_unique($firedRules, SORT_REGULAR)),
            'recommendations'  => array_values(array_unique($recommendations, SORT_REGULAR)),
        ];
    }

    /**
     * @param array<string, mixed> $facts
     * @param list<array<string, mixed>> $conditions
     */
    private function conditionsMatch(array $facts, array $conditions): bool
    {
        foreach ($conditions as $condition) {
            if (! is_array($condition)) {
                return false;
            }

            $field = $condition['field'] ?? null;
            if (! is_string($field) || $field === '') {
                return false;
            }

            if (! array_key_exists($field, $facts)) {
                return false;
            }

            $expected = $condition['equals'] ?? null;
            $actual = $facts[$field];

            if (is_array($expected)) {
                if (! in_array($actual, $expected, true)) {
                    return false;
                }
            } elseif ($actual != $expected) {
                return false;
            }
        }

        return true;
    }
}
