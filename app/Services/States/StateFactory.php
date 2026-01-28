<?php
namespace App\Services\States;

class StateFactory 
{
    public static function getModels($state) 
    {
        $namespace = "App\\Models\\" . ucfirst($state);
        
        return [
            'Course' => $namespace . '\\Course',
            'Chapter' => $namespace . '\\Chapter', 
            'Enrollment' => $namespace . '\\Enrollment',
            'ChapterQuiz' => $namespace . '\\ChapterQuiz',
            'Certificate' => $namespace . '\\Certificate',
            'Progress' => $namespace . '\\Progress',
        ];
    }
    
    public static function getCourse($state) {
        $class = self::getModels($state)['Course'];
        return new $class;
    }
    
    public static function getChapter($state) {
        $class = self::getModels($state)['Chapter'];
        return new $class;
    }
    
    public static function getEnrollment($state) {
        $class = self::getModels($state)['Enrollment'];
        return new $class;
    }
    
    public static function getCertificate($state) {
        $class = self::getModels($state)['Certificate'];
        return new $class;
    }
}