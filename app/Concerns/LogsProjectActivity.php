<?php

namespace App\Concerns;

use App\Enums\ActivityVisibility;
use App\Models\Project;
use App\Services\Projects\ProjectActivityService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait LogsProjectActivity
{
    /**
     * Original tracked values captured before a model update.
     */
    protected array $activityOriginalValues = [];

    public static function bootLogsProjectActivity(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Creating
        |--------------------------------------------------------------------------
        */

        static::creating(
            function (Model $model): void {
                $model->activityOriginalValues = [];
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Created
        |--------------------------------------------------------------------------
        */

        static::created(
            function (Model $model): void {
                if (!$model->shouldLogProjectActivity()) {
                    return;
                }

                $keys = $model->activityAttributeKeys(
                    array_keys(
                        $model->getAttributes()
                    )
                );

                $newValues = Arr::only(
                    $model->getAttributes(),
                    $keys
                );

                /*
                 * Positional arguments are intentional.
                 *
                 * The ProjectActivityService parameter names may differ from
                 * $model, $event, $oldValues and $newValues. PHP named
                 * arguments require an exact parameter-name match.
                 */
                app(ProjectActivityService::class)
                    ->logModelEvent(
                        $model,
                        'created',
                        [],
                        $newValues
                    );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Updating
        |--------------------------------------------------------------------------
        */

        static::updating(
            function (Model $model): void {
                $keys = $model->activityAttributeKeys(
                    array_keys(
                        $model->getDirty()
                    )
                );

                $model->activityOriginalValues =
                    Arr::only(
                        $model->getOriginal(),
                        $keys
                    );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Updated
        |--------------------------------------------------------------------------
        */

        static::updated(
            function (Model $model): void {
                if (!$model->shouldLogProjectActivity()) {
                    $model->activityOriginalValues = [];

                    return;
                }

                $keys = array_keys(
                    $model->activityOriginalValues
                );

                if ($keys === []) {
                    return;
                }

                $newValues = Arr::only(
                    $model->getAttributes(),
                    $keys
                );

                app(ProjectActivityService::class)
                    ->logModelEvent(
                        $model,
                        'updated',
                        $model->activityOriginalValues,
                        $newValues
                    );

                /*
                 * Prevent captured values from leaking into a later update.
                 */
                $model->activityOriginalValues = [];
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Deleted
        |--------------------------------------------------------------------------
        */

        static::deleted(
            function (Model $model): void {
                if (!$model->shouldLogProjectActivity()) {
                    return;
                }

                $keys = $model->activityAttributeKeys(
                    array_keys(
                        $model->getAttributes()
                    )
                );

                $oldValues = Arr::only(
                    $model->getAttributes(),
                    $keys
                );

                app(ProjectActivityService::class)
                    ->logModelEvent(
                        $model,
                        'deleted',
                        $oldValues,
                        []
                    );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Restored
        |--------------------------------------------------------------------------
        |
        | Register the restored event directly through Eloquent's model event
        | system. This avoids calling static::restored() on models such as
        | Payment that do not use SoftDeletes. Such a call can be treated as a
        | dynamic static model call and recursively boot the model.
        |
        | Models using SoftDeletes will fire this event. Other models will
        | simply never fire it.
        |
        */

        static::registerModelEvent(
            'restored',
            function (Model $model): void {
                if (!$model->shouldLogProjectActivity()) {
                    return;
                }

                $keys = $model->activityAttributeKeys(
                    array_keys(
                        $model->getAttributes()
                    )
                );

                $newValues = Arr::only(
                    $model->getAttributes(),
                    $keys
                );

                app(ProjectActivityService::class)
                    ->logModelEvent(
                        $model,
                        'restored',
                        [],
                        $newValues
                    );
            }
        );
    }

    /**
     * Determine whether this model event belongs to a project timeline.
     */
    public function shouldLogProjectActivity(): bool
    {
        return $this->activityProjectId() !== null;
    }

    /**
     * Resolve the project ID associated with the model.
     */
    public function activityProjectId(): ?int
    {
        if ($this instanceof Project) {
            $projectId = $this->getKey();

            return $projectId !== null
                ? (int) $projectId
                : null;
        }

        $projectId = $this->getAttribute(
            'project_id'
        );

        return filled($projectId)
            ? (int) $projectId
            : null;
    }

    /**
     * Return the attributes that should be tracked.
     *
     * Models using this trait may override this method.
     */
    public function activityTrackedAttributes(): array
    {
        return [];
    }

    /**
     * Return attributes that must never be written to project history.
     */
    public function activityIgnoredAttributes(): array
    {
        return [
            'created_at',
            'updated_at',
            'deleted_at',
            'last_activity_at',
            'remember_token',
            'password',
        ];
    }

    /**
     * Return a human-readable subject label.
     *
     * Models using this trait may override this method.
     */
    public function activityLabel(): string
    {
        return Str::headline(
            class_basename($this)
        );
    }

    /**
     * Return the default activity visibility.
     *
     * Models using this trait may override this method.
     */
    public function activityVisibility(): ActivityVisibility
    {
        return ActivityVisibility::Team;
    }

    /**
     * Restrict the activity to a specific user when required.
     */
    public function activityVisibleToUserId(): ?int
    {
        return null;
    }

    /**
     * Filter the supplied attribute keys to their loggable subset.
     */
    protected function activityAttributeKeys(
        array $keys
    ): array {
        $keys = array_values(
            array_diff(
                $keys,
                $this->activityIgnoredAttributes()
            )
        );

        $tracked =
            $this->activityTrackedAttributes();

        if ($tracked !== []) {
            $keys = array_values(
                array_intersect(
                    $keys,
                    $tracked
                )
            );
        }

        return $keys;
    }
}

