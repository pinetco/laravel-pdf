<?php

namespace Pinetco\Pdf;

use Dompdf\Dompdf;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

abstract class Pdf
{
    protected $view;

    protected $paperSize = 'letter';

    protected $orientation = 'portrait';

    public abstract function data();

    public function download()
    {
        $dompdf = new Dompdf;

        $dompdf->setPaper($this->paperSize, $this->orientation);

        $dompdf->loadHtml($this->pdf()->render());

        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Description'       => 'File Transfer',
            'Content-Disposition'       => 'attachment; filename="' . $this->filename() . '"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Type'              => 'application/pdf',
        ]);
    }

    public function setPaperSize($paperSize = 'letter')
    {
        $this->paperSize = $paperSize;

        return $this;
    }

    public function setOrientation($orientation = 'portrait')
    {
        $this->orientation = $orientation;

        return $this;
    }

    public function getPaperSize()
    {
        return $this->paperSize;
    }

    public function getOrientation()
    {
        return $this->orientation;
    }

    public function render()
    {
        return $this->pdf();
    }

    public function pdf()
    {
        return view($this->view(), $this->data());
    }

    public function view()
    {
        return config('pdf.views') . '.' . $this->viewName();
    }

    public function locale($locale)
    {
        app()->setLocale($locale);

        return $this;
    }

    public function viewName()
    {
        if (isset($this->view)) {
            return $this->view;
        }

        return Str::kebab(class_basename($this));
    }

    public function filename()
    {
        return str_replace('_', ' ', Str::title(Str::snake(class_basename($this)))) . '.pdf';
    }

    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }
}
