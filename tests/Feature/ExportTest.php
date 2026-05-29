<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function testCsvExportWithAuthorColumns()
    {
        $author = Author::factory()->create([
            'first_name' => 'Mark',
            'last_name' => 'Twain',
        ]);
        Book::factory()->create(['author_id' => $author->id, 'title' => 'Adventures of Tom Sawyer']);

        $response = $this->get('/export/csv?columns[]=title&columns[]=author');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('first_name', $content);
        $this->assertStringContainsString('last_name', $content);
        $this->assertStringContainsString('Mark', $content);
        $this->assertStringContainsString('Twain', $content);
        $this->assertStringContainsString('Adventures of Tom Sawyer', $content);
    }

    public function testCsvExportWithoutAuthorColumn()
    {
        $author = Author::factory()->create([
            'first_name' => 'Mark',
            'last_name' => 'Twain',
        ]);
        Book::factory()->create(['author_id' => $author->id, 'title' => 'Adventures of Tom Sawyer']);

        $response = $this->get('/export/csv?columns[]=title');

        $response->assertStatus(200);

        $content = $response->streamedContent();

        $this->assertStringContainsString('title', $content);
        $this->assertStringContainsString('Adventures of Tom Sawyer', $content);
        $this->assertStringNotContainsString('first_name', $content);
        $this->assertStringNotContainsString('last_name', $content);
    }

    public function testCsvExportAuthorHasSeparateFirstAndLastNameColumns()
    {
        $author = Author::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Austen',
        ]);
        Book::factory()->create(['author_id' => $author->id]);

        $response = $this->get('/export/csv?columns[]=title&columns[]=author');

        $content = $response->streamedContent();

        $lines = explode("\n", trim($content));
        $header = str_getcsv($lines[0]);

        $this->assertContains('author_first_name', $header);
        $this->assertContains('author_last_name', $header);
    }

    public function testXmlExportWithAuthorSelected()
    {
        $author = Author::factory()->create([
            'first_name' => 'Mark',
            'last_name' => 'Twain',
        ]);
        Book::factory()->create(['author_id' => $author->id, 'title' => 'Adventures of Tom Sawyer']);

        $response = $this->get('/export/xml?columns[]=title&columns[]=author');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');

        $content = $response->getContent();

        $this->assertStringContainsString('<authors>', $content);
        $this->assertStringContainsString('<author>', $content);
        $this->assertStringContainsString('<first_name>Mark</first_name>', $content);
        $this->assertStringContainsString('<last_name>Twain</last_name>', $content);
        $this->assertStringContainsString('<book>', $content);
        $this->assertStringContainsString('<title>Adventures of Tom Sawyer</title>', $content);
    }

    public function testXmlExportWithoutAuthorSelected()
    {
        $author = Author::factory()->create([
            'first_name' => 'Mark',
            'last_name' => 'Twain',
        ]);
        Book::factory()->create(['author_id' => $author->id, 'title' => 'Adventures of Tom Sawyer']);

        $response = $this->get('/export/xml?columns[]=title');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');

        $content = $response->getContent();

        $this->assertStringContainsString('<books>', $content);
        $this->assertStringContainsString('<book>', $content);
        $this->assertStringContainsString('<title>Adventures of Tom Sawyer</title>', $content);
        $this->assertStringNotContainsString('<author>', $content);
        $this->assertStringNotContainsString('<first_name>', $content);
        $this->assertStringNotContainsString('<last_name>', $content);
    }

    public function testXmlExportWithAuthorGroupsBooksUnderAuthor()
    {
        $author = Author::factory()->create([
            'first_name' => 'Mark',
            'last_name' => 'Twain',
        ]);
        Book::factory()->create(['author_id' => $author->id, 'title' => 'Adventures of Tom Sawyer']);
        Book::factory()->create(['author_id' => $author->id, 'title' => 'Adventures of Huckleberry Finn']);

        $response = $this->get('/export/xml?columns[]=title&columns[]=author');

        $content = $response->getContent();

        $xml = simplexml_load_string($content);

        $this->assertEquals('authors', $xml->getName());
        $this->assertCount(1, $xml->author);

        $authorElement = $xml->author[0];
        $this->assertEquals('Mark', (string)$authorElement->first_name);
        $this->assertEquals('Twain', (string)$authorElement->last_name);
        $this->assertCount(2, $authorElement->book);
    }

    public function testXmlExportMultipleAuthorsWithBooks()
    {
        $author1 = Author::factory()->create([
            'first_name' => 'Mark',
            'last_name' => 'Twain',
        ]);
        $author2 = Author::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Austen',
        ]);
        Book::factory()->create(['author_id' => $author1->id, 'title' => 'Adventures of Tom Sawyer']);
        Book::factory()->create(['author_id' => $author2->id, 'title' => 'Pride and Prejudice']);

        $response = $this->get('/export/xml?columns[]=title&columns[]=author');

        $content = $response->getContent();

        $xml = simplexml_load_string($content);

        $this->assertCount(2, $xml->author);

        $this->assertStringContainsString('<first_name>Mark</first_name>', $content);
        $this->assertStringContainsString('<first_name>Jane</first_name>', $content);
        $this->assertStringContainsString('<title>Adventures of Tom Sawyer</title>', $content);
        $this->assertStringContainsString('<title>Pride and Prejudice</title>', $content);
    }

    public function testXmlExportBookTitleIncludedUnderAuthor()
    {
        $author = Author::factory()->create([
            'first_name' => 'Mark',
            'last_name' => 'Twain',
        ]);
        $book = Book::factory()->create(['author_id' => $author->id, 'title' => 'Adventures of Tom Sawyer']);

        $response = $this->get('/export/xml?columns[]=title&columns[]=author');

        $content = $response->getContent();

        $xml = simplexml_load_string($content);
        $bookTitle = (string)$xml->author[0]->book[0]->title;
        $this->assertEquals('Adventures of Tom Sawyer', $bookTitle);
    }

    public function testCsvExportWithMultipleBooksAndAuthors()
    {
        $author1 = Author::factory()->create(['first_name' => 'Mark', 'last_name' => 'Twain']);
        $author2 = Author::factory()->create(['first_name' => 'Jane', 'last_name' => 'Austen']);
        Book::factory()->create(['author_id' => $author1->id, 'title' => 'Tom Sawyer']);
        Book::factory()->create(['author_id' => $author2->id, 'title' => 'Pride and Prejudice']);

        $response = $this->get('/export/csv?columns[]=title&columns[]=author');

        $content = $response->streamedContent();

        $this->assertStringContainsString('Mark', $content);
        $this->assertStringContainsString('Twain', $content);
        $this->assertStringContainsString('Jane', $content);
        $this->assertStringContainsString('Austen', $content);
        $this->assertStringContainsString('Tom Sawyer', $content);
        $this->assertStringContainsString('Pride and Prejudice', $content);
    }
}
