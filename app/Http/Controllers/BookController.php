<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\BookAuthor;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->author) { // Выводим книги конкретного автора
            $booksID = BookAuthor::where('author_id', $request->author)->select('book_id')->get();
            $books = Book::whereIn('id', $booksID)->get();
        } else if ($request->severalGenres == 'yes') { // Выводим книги, где более одного жанра
            $books = Book::where('genre', 'LIKE', '%,%')->get();
        } else if ($request->minDate || $request->maxDate) { // Выводим книги в рамках определенных годов выпуска
             if ($request->minDate && $request->maxDate) { // от и до
                $books = Book::where([['date', '>=', $request->minDate], ['date', '<=', $request->maxDate]])->get();
             } else if ($request->minDate) { // от
                $books = Book::where('date', '>=', $request->minDate)->get();
             } else { // до
                $books = Book::where('date', '<=', $request->maxDate)->get();
             }
        } else { // Выводим все книги
            $books = Book::all();
        }

        foreach ($books as $book) {
            $book->authors = DB::table('book_authors')->where('book_authors.book_id', $book->id)
                ->join('authors', 'authors.id', '=', 'book_authors.author_id')
                ->select('authors.fullName')->get();
        }

        return $books;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:120',
            'description' => 'nullable|max:600',
            'date' => 'required|integer',
            'genre' => 'required',
            'author' => 'required|array'
        ]);

        $book = Book::create([
            'name' => $request->name,
            'description' => $request->description,
            'date' => $request->date,
            'genre' => $request->genre
        ]);

        foreach ($request->author as $author_id) {
            BookAuthor::create([
                'book_id' => $book->id,
                'author_id' => $author_id
            ]);
        }

        return [
            'status'=>'1',
            'msg'=>'success'
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $book = Book::find($id);
        $book->authors = DB::table('book_authors')->where('book_authors.book_id', $book->id)
                ->join('authors', 'authors.id', '=', 'book_authors.author_id')->select('authors.fullName')->get();
        return $book;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:120',
            'description' => 'nullable|max:600',
            'genre' => 'required',
            'date' => 'required|integer',
        ]);

        Book::find($id)->update([
            'name' => $request->name,
            'description' => $request->description,
            'genre' => $request->genre,
            'date' => $request->date
        ]);

        return [
            'status'=>'1',
            'msg'=>'success'
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Book::find($id)->delete();

        return [
            'status'=>'1',
            'msg'=>'success'
        ];
    }
}
