<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class PlaylistCurator implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return "You are a professional music curator.
                Your goal is to provide a list of 10 songs based on the user's mood.
                You must return ONLY a raw JSON object.
                Strictly NO conversational text, NO markdown blocks (like ```json), and NO explanations.
                The response must follow this EXACT structure:
                    {
                        \"playlist_name\": \"a short, creative summary of the songs in lowercase\",
                        \"tracks\": [
                            {\"artist\": \"Artist Name\", \"title\": \"Song Title\"}
                        ]
                    }";
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }
}
