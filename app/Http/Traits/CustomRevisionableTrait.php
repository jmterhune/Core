<?php namespace App\Http\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Venturecraft\Revisionable\Revisionable;
use Venturecraft\Revisionable\RevisionableTrait;


/*
 * This file is part of the Revisionable package by Venture Craft
 *
 * (c) Venture Craft <http://www.venturecraft.com.au>
 *
 */

/**
 * Class RevisionableTrait
 * @package Venturecraft\Revisionable
 */
trait CustomRevisionableTrait
{
    use RevisionableTrait;


    /**
     * Called after a model is successfully saved.
     *
     * @return void
     */
    public function postSave()
    {
        if (isset($this->historyLimit) && $this->revisionHistory()->count() >= $this->historyLimit) {
            $LimitReached = true;
        } else {
            $LimitReached = false;
        }
        if (isset($this->revisionCleanup)){
            $RevisionCleanup=$this->revisionCleanup;
        }else{
            $RevisionCleanup=false;
        }

        // check if the model already exists
        if (((!isset($this->revisionEnabled) || $this->revisionEnabled) && $this->updating) && (!$LimitReached || $RevisionCleanup)) {
            // if it does, it means we're updating

            $changes_to_record = $this->changedRevisionableFields();

            $revisions = array();

            foreach ($changes_to_record as $key => $change) {
                $original = array(
                    'revisionable_type' => $this->getMorphClass(),
                    'revisionable_id' => $this->getKey(),
                    'key' => $key,
                    'old_value' => Arr::get($this->originalData, $key),
                    'new_value' => $this->updatedData[$key],
                    'user_id' => $this->getSystemUserId(),
                    'user_type' => (function_exists('backpack_auth') && backpack_auth()->check()) ? 'App\Models\User' : 'App\Models\Attorney',
                    'created_at' => new \DateTime(),
                    'updated_at' => new \DateTime(),
                );

                $revisions[] = array_merge($original, $this->getAdditionalFields());
            }

            if (count($revisions) > 0) {
                if($LimitReached && $RevisionCleanup){
                    $toDelete = $this->revisionHistory()->orderBy('id','asc')->limit(count($revisions))->get();
                    foreach($toDelete as $delete){
                        $delete->delete();
                    }
                }
                $revision = Revisionable::newModel();
                DB::table($revision->getTable())->insert($revisions);
                Event::dispatch('revisionable.saved', array('model' => $this, 'revisions' => $revisions));
            }
        }
    }

    /**
     * Called after record successfully created
     */
    public function postCreate()
    {

        // Check if we should store creations in our revision history
        // Set this value to true in your model if you want to
        if(empty($this->revisionCreationsEnabled))
        {
            // We should not store creations.
            return false;
        }

        if ((!isset($this->revisionEnabled) || $this->revisionEnabled))
        {
            $revisions[] = array(
                'revisionable_type' => $this->getMorphClass(),
                'revisionable_id' => $this->getKey(),
                'key' => self::CREATED_AT,
                'old_value' => null,
                'new_value' => $this->{self::CREATED_AT},
                'user_id' => $this->getSystemUserId(),
                'user_type' => (function_exists('backpack_auth') && backpack_auth()->check()) ? 'App\Models\User' : 'App\Models\Attorney',
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            );

            //Determine if there are any additional fields we'd like to add to our model contained in the config file, and
            //get them into an array.
            $revisions = array_merge($revisions[0], $this->getAdditionalFields());

            $revision = Revisionable::newModel();
            \DB::table($revision->getTable())->insert($revisions);
            \Event::dispatch('revisionable.created', array('model' => $this, 'revisions' => $revisions));
        }

    }

    /**
     * If softdeletes are enabled, store the deleted time
     */
    public function postDelete()
    {
        if ((!isset($this->revisionEnabled) || $this->revisionEnabled)
            && $this->isSoftDelete()
            && $this->isRevisionable($this->getDeletedAtColumn())
        ) {
            $revisions[] = array(
                'revisionable_type' => $this->getMorphClass(),
                'revisionable_id' => $this->getKey(),
                'key' => $this->getDeletedAtColumn(),
                'old_value' => null,
                'new_value' => $this->{$this->getDeletedAtColumn()},
                'user_id' => $this->getSystemUserId(),
                'user_type' => (function_exists('backpack_auth') && backpack_auth()->check()) ? 'App\Models\User' : 'App\Models\Attorney',
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            );

            //Since there is only one revision because it's deleted, let's just merge into revision[0]
            $revisions = array_merge($revisions[0], $this->getAdditionalFields());

            $revision = Revisionable::newModel();
            \DB::table($revision->getTable())->insert($revisions);
            \Event::dispatch('revisionable.deleted', array('model' => $this, 'revisions' => $revisions));
        }
    }

    /**
     * If forcedeletes are enabled, set the value created_at of model to null
     *
     * @return void|bool
     */
    public function postForceDelete()
    {
        if (empty($this->revisionForceDeleteEnabled)) {
            return false;
        }

        if ((!isset($this->revisionEnabled) || $this->revisionEnabled)
            && (($this->isSoftDelete() && $this->isForceDeleting()) || !$this->isSoftDelete())) {

            $revisions[] = array(
                'revisionable_type' => $this->getMorphClass(),
                'revisionable_id' => $this->getKey(),
                'key' => self::CREATED_AT,
                'old_value' => $this->{self::CREATED_AT},
                'new_value' => null,
                'user_id' => $this->getSystemUserId(),
                'user_type' => (function_exists('backpack_auth') && backpack_auth()->check()) ? 'App\Models\User' : 'App\Models\Attorney',
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            );

            $revision = Revisionable::newModel();
            \DB::table($revision->getTable())->insert($revisions);
            \Event::dispatch('revisionable.deleted', array('model' => $this, 'revisions' => $revisions));
        }
    }


}
