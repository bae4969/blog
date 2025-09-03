// 전역 변수
let userInfo = null;
let showState = 0; // 0: 초기, 1: 1열, 2: 2열
let categoryIndex = -1;
let searchString = "";
let pageIndex = 0;
let pageSize = 10;
let pageCount = 0;
let loadCount = 0;

// 레이아웃 전환 제어(히스테리시스 & 디바운스)
const BREAKPOINT = 1600;
const HYSTERESIS = 50; // px 여유 구간: 1550~1650에서는 기존 레이아웃 유지
let resizeTimer = null;
let relayoutTimer = null;

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    // URL 파라미터 파싱
    const params = new URLSearchParams(window.location.search);
    if (params.get('category_index')) {
        categoryIndex = parseInt(params.get('category_index'));
    }
    if (params.get('search_string')) {
        searchString = decodeURIComponent(params.get('search_string'));
    }
    if (params.get('page_index')) {
        pageIndex = parseInt(params.get('page_index'));
    }

    // 초기화 함수들
    verifyLogin();
    initProfile();
    initCategoryList();
    
    // 현재 페이지에 따라 다른 초기화
    const currentPath = window.location.pathname;
    if (currentPath === '/index.php' || currentPath === '/') {
        initPostingList();
        // 초기 게시글 배치
        setPostingList(true);
        attachPostImageHandlers();
        observePostings();
    } else if (currentPath === '/reader.php') {
        initPostingDetail();
    }
});

function scheduleRelayout(delay = 120) {
    if (relayoutTimer) cancelAnimationFrame(relayoutTimer);
    relayoutTimer = requestAnimationFrame(() => {
        setTimeout(() => setPostingList(false), delay);
    });
}

function attachPostImageHandlers() {
    const images = document.querySelectorAll('#postings img.posting_thumbnail');
    images.forEach(img => {
        if (img.dataset._relayoutAttached === '1') return;
        img.dataset._relayoutAttached = '1';
        if (!img.complete) {
            img.addEventListener('load', () => scheduleRelayout(50));
            img.addEventListener('error', () => scheduleRelayout(50));
        }
    });
}

function observePostings() {
    const root = document.getElementById('postings');
    if (!root) return;
    const observer = new MutationObserver(() => {
        attachPostImageHandlers();
        scheduleRelayout(80);
    });
    observer.observe(root, { childList: true, subtree: true });
}

// 로그인 상태 확인
function verifyLogin() {
    fetch('/get/login_verify')
        .then(response => response.json())
        .then(data => {
            userInfo = data;
            updateLoginStatus();
        })
        .catch(error => {
            console.error('로그인 상태 확인 실패:', error);
        });
}

// 로그인 상태 UI 업데이트
function updateLoginStatus() {
    const topRight = document.getElementById('topRight');
    const topWrite = document.getElementById('topWrite');
    
    if (topRight) {
        topRight.textContent = userInfo && userInfo.state === 0 ? '로그아웃' : '로그인';
    }
    
    if (topWrite) {
        if (userInfo && userInfo.state === 0 && userInfo.can_write === 1) {
            topWrite.textContent = '글쓰기';
            topWrite.style.display = 'block';
        } else {
            topWrite.style.display = 'none';
        }
    }
}

// 프로필 초기화
function initProfile() {
    // 프로필 정보는 서버에서 렌더링되므로 여기서는 추가 작업 없음
}

// 카테고리 목록 초기화
function initCategoryList() {
    // 카테고리 목록은 서버에서 렌더링되므로 여기서는 추가 작업 없음
}

// 게시글 목록 초기화
function initPostingList() {
    // 게시글 목록은 서버에서 렌더링되므로 여기서는 추가 작업 없음
}

// 게시글 상세 초기화
function initPostingDetail() {
    // 게시글 상세는 서버에서 렌더링되므로 여기서는 추가 작업 없음
}

// 로그인/로그아웃 클릭
function loginoutClick() {
    if (userInfo && userInfo.state === 0) {
        if (confirm('로그아웃하시겠습니까?')) {
            location.href = '/logout.php';
        }
    } else {
        location.href = '/login.php';
    }
}

// 글쓰기 클릭
function writePostingClick() {
    if (userInfo && userInfo.state === 0 && userInfo.can_write === 1) {
        location.href = `/writer.php${categoryIndex > 0 ? '?category_index=' + categoryIndex : ''}`;
    } else {
        alert('글쓰기 권한이 없습니다.');
    }
}

// 게시글 검색
function searchPostingClick() {
    const categorySelect = document.getElementById('search_category_list');
    const searchText = document.getElementById('search_posting_text');
    
    if (!categorySelect || !searchText) return;
    
    const selectedCategory = categorySelect.value;
    const searchValue = searchText.value.trim();
    
    let url = '/index.php?';
    if (selectedCategory !== '-1') {
        url += 'category_index=' + selectedCategory + '&';
    }
    if (searchValue) {
        url += 'search_string=' + encodeURIComponent(searchValue);
    }
    
    location.href = url;
}

// 게시글 삭제
function deletePost(postId) {
    if (confirm('정말로 이 게시글을 삭제하시겠습니까?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/post/delete/${postId}`;
        
        // CSRF 토큰 추가
        const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
        if (csrfToken) {
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = 'csrf_token';
            tokenInput.value = csrfToken;
            form.appendChild(tokenInput);
        }
        
        document.body.appendChild(form);
        form.submit();
    }
}

// 페이지 리사이즈 처리(디바운스)
window.addEventListener('resize', function() {
    if (resizeTimer) clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => scheduleRelayout(0), 150);
});

// 게시글 목록 설정 (히스테리시스 적용)
function shouldUseTwoCols(currentWidth, currentState) {
    // 현재 2열이면 1550px 이하로 줄어들 때만 1열로 변경
    if (currentState === 2) return currentWidth >= (BREAKPOINT - HYSTERESIS);
    // 현재 1열이면 1650px 이상 넓어질 때만 2열로 변경
    if (currentState === 1) return currentWidth >= (BREAKPOINT + HYSTERESIS);
    // 초기 상태: 단순 기준으로 결정
    return currentWidth >= BREAKPOINT;
}

function setPostingList(isInitial = false) {
    const left = document.getElementById('left');
    const right = document.getElementById('right');
    const temp = document.getElementById('temp');
    const postingsRoot = document.getElementById('postings');
    if (!left || !right || !postingsRoot) return;

    const allPosts = Array.from(postingsRoot.querySelectorAll('.posting, .posting_ban'));
    if (allPosts.length === 0) return;

    const width = document.body.offsetWidth;
    const wantTwoCols = shouldUseTwoCols(width, showState);

    if (!wantTwoCols) {
        if (showState !== 1 || isInitial) {
            showState = 1;
            if (temp) temp.style.height = 0;
            left.style.width = '100%';
            right.style.width = '0%';
            // 모든 포스팅을 왼쪽 단일 열로 이동
            allPosts.forEach(post => left.appendChild(post));
        }
        return;
    }

    // 2열 배치
    if (showState !== 2 || isInitial) {
        showState = 2;
        if (temp) temp.style.height = 0;
        left.style.width = '50%';
        right.style.width = '50%';

        // 먼저 두 컬럼 비우기
        while (left.firstChild) left.removeChild(left.firstChild);
        while (right.firstChild) right.removeChild(right.firstChild);

        // 높이가 더 낮은 쪽에 순서대로 배치
        allPosts.forEach(post => {
            const leftHeight = left.offsetHeight;
            const rightHeight = right.offsetHeight;
            if (leftHeight <= rightHeight) {
                left.appendChild(post);
            } else {
                right.appendChild(post);
            }
        });
    }
}

// 유틸리티 함수들
function setCookie(name, value, hours = 2) {
    const date = new Date();
    date.setTime(date.getTime() + hours * 3600000);
    document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/`;
}

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
        const cookieValue = parts.pop().split(';').shift();
        setCookie(name, cookieValue, 2); // 쿠키 갱신
        return cookieValue;
    }
    return null;
}

function deleteCookie(name) {
    setCookie(name, '', -1);
}

// AJAX 요청 헬퍼 함수
function makeRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    return fetch(url, finalOptions)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        });
}

// 에러 처리
window.addEventListener('error', function(e) {
    console.error('JavaScript 에러:', e.error);
});

// 페이지 언로드 시 정리
window.addEventListener('beforeunload', function() {
    // 필요한 정리 작업
});
