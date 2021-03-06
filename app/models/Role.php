<?php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
class Role extends Model
{
    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];
    
    /**
     * Model 'JobTitle' table
     * @var string
     */
    protected $table = 'vote_roles';

   /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array('name');

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = array('id');

}