<?php

namespace Tests\Feature;

use Tests\TestCase;

class RefinitivLetterLinksTest extends TestCase
{
    public function test_refinitiv_form_offers_both_letter_templates_and_removes_the_old_link(): void
    {
        $response = $this->get(route('refinitiv.create'));

        $response->assertOk()
            ->assertSee('Surat Keperluan Penelitian')
            ->assertSee('Surat Keperluan Tugas')
            ->assertSee('Surat Keperluan Penelitian atau Tugas')
            ->assertSee('href="https://docs.google.com/document/d/1mTqZmQLz7nkjlTzrDx2IvQptaT3g7HeD/edit?usp=sharing&amp;ouid=116852591645933169903&amp;rtpof=true&amp;sd=true"', false)
            ->assertSee('href="https://docs.google.com/document/d/1bOwgWpMdqATxTJ6lx262xnnH14Mm66Yr/edit?usp=sharing&amp;ouid=116852591645933169903&amp;rtpof=true&amp;sd=true"', false)
            ->assertDontSee('https://bit.ly/SuratPernyataanKesanggupanMenjagaInformasi');
    }
}
