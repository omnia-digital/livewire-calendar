<div class="flex-1 h-6 md:h-12 border -mt-px -ml-px flex items-center justify-center md:bg-indigo-100 text-gray-500 md:text-gray-900">
    <p class="text-[10px] font-semibold uppercase tracking-wider md:text-sm md:font-normal md:tracking-normal md:normal-case">
        <span class="md:hidden">{{ substr($day->format('l'), 0, 1) }}</span>
        <span class="hidden md:inline lg:hidden">{{ $day->format('D') }}</span>
        <span class="hidden lg:inline">{{ $day->format('l') }}</span>
    </p>
</div>
