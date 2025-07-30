<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Timeslot extends Model
{
    use HasFactory, CrudTrait, SoftDeletes;

    protected $appends = ['title','update_url', 'edit_url', 'delete_url','display','date','color','start_time','end_time','total_length','clickable'];

    protected $fillable = ['start','end','quantity','description','category_id','duration','allDay','blocked','template_id','public_block','block_reason'];

    protected function title(): Attribute
    {
        $start = Carbon::create($this->start)->timezone('America/New_York');
        $end = Carbon::create($this->end)->timezone('America/New_York');
        $diff = $start->diffInMinutes($end);
        $available = $this->quantity * $this->duration;
        $events = TimeslotEvent::where('timeslot_id', $this->id)->get();

        $title = '';

        if($this->events->count() * $this->duration  > $diff && $this->events->count() != $this->quantity){
            $host = $_SERVER['SERVER_NAME'];
            if($host == 'jacs-new.flcourts18.org'){

                $title = $this->quantity - $this->events->count() . ' Available';

                if(isset($this->category->description)){
                    $title .= ' (' . $this->category->description . ')';
                }

                if(isset($this->description)){
                    $title .= ' (' . $this->description . ')';
                }

                $title = $this->allDay ? $this->description : $title;
            } else{
                if($this->blocked){
                    if($this->public_block)
                    {
                        $title =  'Public Blocked <Br>' . ($this->block_reason != null ? $this->block_reason : $this->description);
                    }
                    else{
                        $title =  'Blocked <Br>' . ($this->block_reason != null ? $this->block_reason : $this->description);
                    }
                } else {
                    $availabe_count = (floor($diff / $this->duration) - $events->count());

                    $count = $availabe_count > 0
                        ? $availabe_count . ' Available <Br> ' . $this->quantity - floor($diff / $this->duration) . ' Overbooked'
                        : $this->events->count() - floor($diff / $this->duration) . ' Overbooked';

                    $title = $this->allDay
                        ? $this->description
                        : $count;
                }
                $title .= '<br>';
                foreach ($this->events as $event){
                    $title .= $event->case_num . '<br>';
                }
            }

        }else{
            if($this->events->count() == $this->quantity){
                foreach ($this->events as $event){
                    $title .= $event->case_num . '<br>';
                }
            } else{
                if($this->blocked){
                    if($this->public_block)
                    {
                        $title =  'Public Blocked <Br>' . ($this->block_reason != null ? $this->block_reason : $this->description);
                    }
                    else{
                        $title =  'Blocked <Br>' . ($this->block_reason != null ? $this->description : $this->description);
                    }
                } else{
                    if($this->quantity - $this->events->count() < 1){
                        foreach ($this->events as $event){
                            $title .= $event->case_num . '<br>';
                        }
                    } else{
                        $title =  $this->allDay ? $this->description : ($this->quantity - $this->events->count()) . ' Available';
                        if($this->category != null){
                            $title .= ' (' . $this->category->description . ')';
                        }

                        if($this->description != null){
                            $title .= ' (' . $this->description . ')';
                        }

                        $title = $this->allDay ? $this->description : $title;
                    }
                }

                $title .= '<br>';
                foreach ($this->events as $event){
                    $title .= $event->case_num . '<br>';
                }

            }

        }

        return new Attribute(
            get: fn() => $title
        );
    }

    protected function updateUrl(): Attribute
    {
        return new Attribute(
            get: fn() => route('timeslot.update', $this->id)
        );
    }

    protected function editUrl(): Attribute
    {
        return new Attribute(
            get: fn() => route('timeslot.edit', $this->id)
        );
    }

    protected function deleteUrl(): Attribute
    {
        return new Attribute(
            get: fn() => route('timeslot.destroy', $this->id)
        );
    }

    protected function getDisplayAttribute(){
        return 'auto';
    }

    protected function getColorAttribute(){
        $color = null;

        $start = Carbon::create($this->start)->timezone('America/New_York');
        $end = Carbon::create($this->end)->timezone('America/New_York');
        $diff = $start->diffInMinutes($end);
        $available = $this->quantity * $this->duration;

        if($available > $diff && ($_SERVER['SERVER_NAME'] != 'jacs.flcourts18.net') && $this->events->count() != $this->quantity){
            if($this->blocked){
                $color = '#808080';
            } else {
                $color = '#dc3545';
            }
        }else{
            if($this->events->count() == $this->quantity){
                $color = '#28a745';
            } else{
                if($this->blocked){
                    if($this->blocked && $this->public_block)
                    {
                        $color = "rgba(0, 0, 255, 0.5)";
                    }
                    else{
                        $color = '#808080';
                    }
                } else{
                    if($this->quantity - $this->events->count() < 1){
                        $color = '#dc3545';
                    } else{
                        $color = '#007bff';
                    }
                }

            }

        }

        return $color;
    }

    protected function getDateAttribute(){
        $date = Carbon::create($this->start);
        return $date->format('m/d/Y');
    }

    protected function getStartTimeAttribute(){
        $date = Carbon::create($this->start);
        return $date->format('g:i a');
    }

    protected function getEndTimeAttribute(){
        $date = Carbon::create($this->end);
        return $date->format('g:i a');
    }

    protected function getLengthAttribute(){

        $start = Carbon::create($this->start);
        $end = Carbon::create($this->start)->addminutes($this->duration);

        return $start->longAbsoluteDiffForHumans($end,2);
    }

    protected function getTotalLengthAttribute(){

        $start = Carbon::create($this->start);
        $end = Carbon::create($this->start)->addminutes($this->duration * $this->quantity);

        return $start->longAbsoluteDiffForHumans($end);
    }

    protected function getTableDisplayAttribute(){
        $date = Carbon::create($this->start);
        return $date->format('m/d/Y') . ' @ ' . $date->format('g:i a');
    }

    public function getCategoryTableAttribute(){
        if(isset($this->category_id)){
            return Category::find($this->category_id)->description;
        } else{
            return '-';
        }
    }

    public function events(){
        return $this->hasManyThrough(
            Event::class,
            TimeslotEvent::class,
            'timeslot_id','id',
            'id','event_id'
        );
    }

    public function eventstest(){
        return $this->hasManyThrough(
            Event::class,
            TimeslotEvent::class,
            'timeslot_id','id',
            'id','event_id'
        );
    }

    public function court(){
        return $this->hasOneThrough(
            Court::class,
            CourtTimeslot::class,
            'timeslot_id','id',
            'id','court_id'
        );
    }

    public function template(){
        return $this->belongsTo(Template::class,'template_id','id');
    }

    public function getCourtTableAttribute(){

        $court_timeslot = CourtTimeslot::where('timeslot_id', $this->id)->first();

        $court = Court::find($court_timeslot->court_id);

        return $court->description;
    }

    public function getAvailableAttribute(){
        if($this->blocked || $this->public_block){
            return false;
        } else{
            return $this->quantity > $this->events->count();
        }
    }


    public function scopeActive($query)
    {
        $count = $this->court()->count();

        return $query->get()->where('quantity' <= $count);
    }

    public function motions(){
        return $this->morphMany(TimeslotMotion::class, 'timeslotable');
    }

    public function category(){
        return $this->belongsTo('App\Models\Category', 'category_id','id');
    }
    public function getCreateEventURL(){
        $court_timeslot = CourtTimeslot::where('timeslot_id',$this->id)->first();
         return '<a href="'. url('calendar/'.$court_timeslot->court_id.'?create_event='. $court_timeslot->timeslot_id) .'" class="btn btn-sm btn-link" data-button-type="delete"><i class= "la la-calendar"></i> Modify Timeslot or  Schedule a Hearing </a>';
    }

    protected function getClickableAttribute()
    {
        return !$this->public_block;
    }



}
