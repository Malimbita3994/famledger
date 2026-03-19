<!-- resources/views/components/back-to-top.blade.php -->
<button id="back-to-top" class="fixed bottom-4 right-4 hidden btn">
  <i class="ki-filled ki-arrow-up"></i>
</button>

<script>
  document.addEventListener('scroll', () => {
    const btn = document.getElementById('back-to-top');
    if (window.scrollY > 300) btn.classList.remove('hidden');
    else btn.classList.add('hidden');
  });
  document.getElementById('back-to-top').addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
</script>
