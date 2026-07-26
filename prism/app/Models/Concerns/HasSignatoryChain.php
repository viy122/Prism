<?php

namespace App\Models\Concerns;

/**
 * Shared signatory-chain behavior for PR / AOC / PO documents.
 *
 * Consuming models define:
 *   const SIGNATORY_DOC_PREFIX = 'PR';
 *   const SIGNATORY_STAGES = [
 *       ['key' => 'draft', 'label' => 'Created', 'type' => 'routing'],
 *       ...
 *       ['key' => 'fully_signed', 'label' => 'Fully Signed', 'type' => 'signature'],
 *   ];
 *
 * type 'signature' stages are completed by signing ('signed' log action);
 * type 'routing' stages are pass-through steps ('forwarded' log action).
 */
trait HasSignatoryChain
{
    public static function signatoryStages(): array
    {
        return array_column(static::SIGNATORY_STAGES, 'key');
    }

    public static function signatoryStageMeta(): array
    {
        return static::SIGNATORY_STAGES;
    }

    /** Stage meta with per-instance dynamic labels resolved. */
    public function resolvedStageMeta(): array
    {
        return array_map(fn (array $meta) => $this->resolveStageLabel($meta), static::SIGNATORY_STAGES);
    }

    /** Hook for models whose stage labels depend on instance state. */
    protected function resolveStageLabel(array $meta): array
    {
        return $meta;
    }

    public function stageMetaFor(?string $key): ?array
    {
        foreach ($this->resolvedStageMeta() as $meta) {
            if ($meta['key'] === $key) {
                return $meta;
            }
        }
        return null;
    }

    /** Index of a stage key within the chain (0 for draft/unknown). */
    public function stageIndex(?string $key): int
    {
        $idx = array_search($key, static::signatoryStages());
        return $idx === false ? 0 : $idx;
    }

    public function nextSignatoryStage(): ?string
    {
        $stages = static::signatoryStages();
        $idx    = array_search($this->signatory_stage, $stages);
        return ($idx !== false && $idx < count($stages) - 1) ? $stages[$idx + 1] : null;
    }

    /** Log action for completing the CURRENT stage. */
    public function advanceAction(): string
    {
        $meta = $this->stageMetaFor($this->signatory_stage);
        return ($meta['type'] ?? 'signature') === 'routing' ? 'forwarded' : 'signed';
    }

    /**
     * Role code (roles.code) whose queue owns a stage, or null when the stage
     * is driven centrally by the Procurement Office. Stages opt in via a
     * 'role' key in SIGNATORY_STAGES; models may override for dynamic stages.
     */
    public function stageOwnerRole(?string $stageKey): ?string
    {
        return $this->stageMetaFor($stageKey)['role'] ?? null;
    }

    public function getSignatoryLabelAttribute(): string
    {
        $meta = $this->stageMetaFor($this->signatory_stage);
        if (!$meta) {
            return ucfirst(str_replace('_', ' ', $this->signatory_stage ?? 'draft'));
        }
        return match ($meta['key']) {
            'draft'        => static::SIGNATORY_DOC_PREFIX . ' Created',
            'fully_signed' => static::SIGNATORY_DOC_PREFIX . ' – Fully Signed',
            default        => static::SIGNATORY_DOC_PREFIX . ' – At ' . $meta['label'],
        };
    }

    /** Human-readable line for a signature-log row. */
    public function describeSignatureLog($log): string
    {
        $stages = static::signatoryStages();
        $key    = $stages[$log->signatory_number] ?? null;
        $label  = $this->stageMetaFor($key)['label'] ?? ucfirst(str_replace('_', ' ', (string) $key));

        return match ($log->action) {
            'forwarded' => 'Forwarded to '
                . ($this->stageMetaFor($stages[$log->signatory_number + 1] ?? null)['label'] ?? 'next stage'),
            'returned'  => 'Returned to '
                . ($this->stageMetaFor($stages[max(0, $log->signatory_number - 1)] ?? null)['label'] ?? 'draft')
                . ' (was at ' . $label . ')',
            default     => 'Signed – ' . $label,
        };
    }
}
