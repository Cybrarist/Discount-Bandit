{!! $style !!}

<div class="w-full bg-gray-200 dark:bg-gray-700 h-1 rounded-full overflow-hidden shadow-inner">
    <div class="h-full rounded-full transition-width duration-400 ease-in-out"
        style="width: {{ $percent }}%; background-color: {{ $progressColor }};">
    </div>
</div>
<div class="items-center mt-4 w-full">
    <span class="w-full text-gray-800 dark:text-gray-200">{{ $percent }}% </span>
</div>
