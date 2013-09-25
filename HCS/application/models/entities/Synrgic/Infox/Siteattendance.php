<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_siteattendance")
 */
class Siteattendance extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * 
     * @ManyToOne(targetEntity="Synrgic\Infox\Workerdetails")
     */
    protected $worker;

    /**
     * the working month
     *
     * @Column(type="date", nullable=true)
     */
    protected $month;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $day1;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $day2;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day3;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day4;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day5;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $day6;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $day7;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day8;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day9;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day10;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $day11;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $day12;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day13;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day14;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day15;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day16;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day17;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day18;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day19;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day20;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day21;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day22;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day23;
    /**
     * @Column(type="string", nullable=true)
     */
    protected $day24;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $day25;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $day26;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $day27;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $day28;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $day29;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $day30;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $day31;

    
}
