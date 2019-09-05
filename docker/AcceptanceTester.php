<?php

class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    /**
     * @When I am on url :url
     */
    public function IamOnUrl($url)
    {
        $this->amOnUrl($url);
    }

    /**
     * @Then I see element :element
     */
    public function IseeElement($element)
    {
        $this->waitForElementVisible($element);
        $this->seeElement($element);
    }

    /**
     * @Then I wait for text :element
     */
    public function IwaitForText($text)
    {
        $this->waitForText($text);
    }

    /**
     * @Then I click :element
     */
    public function IclickElement($element)
    {
        $this->click($element);
    }

    /**
     * @Then I fill field :field with :text
     */
    public function IfillFieldWithText($field, $text)
    {
        $this->fillField($field, $text);
    }
}
