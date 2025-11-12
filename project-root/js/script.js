document.addEventListener('DOMContentLoaded', () => {
  const likeButtons = document.querySelectorAll('.like-buttons');

  likeButtons.forEach(buttonGroup => {
    const postId = buttonGroup.dataset.postId;

    const likeBtn = buttonGroup.querySelector('.like-btn');
    const dislikeBtn = buttonGroup.querySelector('.dislike-btn');
    const statusText = buttonGroup.nextElementSibling;

    likeBtn.addEventListener('click', () => {
      sendReaction(postId, 1, statusText);
    });

    dislikeBtn.addEventListener('click', () => {
      sendReaction(postId, 0, statusText);
    });
  });
});

function sendReaction(postId, liked, statusElement) {
  fetch('like.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: `post_id=${encodeURIComponent(postId)}&liked=${liked}`
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      statusElement.textContent = liked ? 'You liked this post.' : 'You disliked this post.';
    } else {
      statusElement.textContent = 'Action failed: ' + data.message;
    }
  })
  .catch(() => {
    statusElement.textContent = 'Network error.';
  });
}
