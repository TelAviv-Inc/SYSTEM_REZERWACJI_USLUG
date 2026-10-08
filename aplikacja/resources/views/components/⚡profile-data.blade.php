<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>


<div class="mt-2">
    <div class="rounded-lg bg-white border border-brand-border px-3 py-2">
        <div class="flex flex-col gap-1.5 ">
            <h2 class="text-lg font-bold text-brand-text">Dane osobiste</h2>
            <p class="text-sm italic text-brand-muted">Dane tylko do odczytu.</p>
        </div>

        <form action="" method="post" class="flex justify-start items-start mt-2">
            <div class="flex flex-col gap-1 p-2">
                        <label for="email">Adres e-mail</label>
                     <input type="email" name="email" id=""> 
            </div>
            <label for="email">Numer telefonu</label>
            <input type="phone" name="email" id="">
        </form>
    </div>

</div>