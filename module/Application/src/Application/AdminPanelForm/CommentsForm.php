<?php
/*
 * Application name: OpenRate-it! A general-purpose polling platform Copyright (C) 2014 Alain Bindele (alain.bindele@gmail.com)
 * This file is part of Rate-it!
 * Rate-it! is free software; you can redistribute
 * it and/or modify it under the terms of the GNU
 * General Public License as published by the
 * Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * Rate-it! is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street,
 * Fifth Floor, Boston, MA 02110-1301, USA.
 */

/**
 * @package openrate-it!
 * @author Alain Bindele
 * @version alpha 0.2
 */
namespace Application\AdminPanelForm;

use Zend\Form\Element;
use Zend\Form\Form;

/**
 * This class defines the comments Form data structure
 * e.g. this kind of form is used in the
 * /view/content/admin-panel/input-panel/rates/comments.phtml
 * view file to add a comment to a survey
 * Class CommentsForm
 * @package Application\AdminPanelForm
 */

class CommentsForm extends Form{

    /**
     * These are the items of the dropdown menu
     * used to select users and surveys
     */
    protected $usersItems = array(
        'select user' => 'Select user'
    );

    protected $surveysItems = array(
        'select survey' => 'Select survey'
    );

    /**
     * The constructor of the class takes a user collection
     * and a survey collection and set them in the local variables
     * of the form.
     * The users collection is filled with user unique identifier
     * and the name
     * The survey collection with the unique identifier and the title
     * @param array $usersCollection
     * @param array $surveyCollection
     */

    function __construct( $usersCollection = array(), $surveyCollection = array() )
    {
        /**
         * If some user exists add it to the local variable $this->usersItems
         */
        if (sizeof($usersCollection) > 0) {
            foreach ($usersCollection as $u) {
                $this->usersItems[$u->getId()] = $u->getId() . " - " . $u->getName();
            }
        }
        /**
         * If some survey exist add it to the local variable $this->surveysItems
         */
        if (sizeof($surveyCollection) > 0) {
            foreach ($surveyCollection as $s) {
                $this->surveysItems[$s->getId()] = $s->getId() . " - " . $s->getTitle();
            }
        }
        //return the new object
        return $this;
    }

    /**
     * Returns the form compiled with the view data structures
     * useful to add a new comment to the selected survey
     * @return Form
     */
    function getAddCommentsForm()
    {
        //Create the user Select dropdown list
        $user = new Element\Select('commentUserId');
        $user->setLabel('User')->setOptions(array(
            'value_options' => $this->usersItems
        ))-> setAttribute('id','commentUserId');

        //Create the survey Select dropdown list
        $survey = new Element\Select('commentSurveyId');
        $survey -> setLabel('Survey')->setOptions(array(
            'value_options' => $this->surveysItems
        ))-> setAttribute('id','commentSurveyId');

        //Create the Text Area to fill with comments
        $comment = new Element\Textarea('commentText');
        $comment -> setLabel('Comment')
            -> setAttribute('id','commentId');

        //create send button
        $button = new Element\Image('send');
        $button->setLabel('+')->setAttributes(array(
            'src' => 'img/ico/check.png',
            'width' => '25',
            'height' => '25',
            'value' => 'Add Item',
        ));

        //put all together
        $form = new Form('addCommentsForm');
        $form->add($user)
            ->add($survey)
            ->add($comment)
            ->add($button);
        return $form;
    }
};


