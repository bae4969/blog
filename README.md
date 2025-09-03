# 리팩토링된 PHP 블로그 애플리케이션

## 개요

이 프로젝트는 기존의 단순한 PHP 블로그를 현대적인 MVC 아키텍처로 리팩토링한 것입니다.

## 주요 개선사항

### 1. 아키텍처 개선
- **MVC 패턴 적용**: Model, View, Controller 분리
- **PSR-4 오토로딩**: Composer를 통한 의존성 관리
- **네임스페이스 사용**: 코드 구조화 및 충돌 방지

### 2. 보안 강화
- **PDO 사용**: SQL 인젝션 방지를 위한 Prepared Statements
- **CSRF 토큰**: Cross-Site Request Forgery 방지
- **입력값 검증**: XSS 및 기타 공격 방지
- **세션 기반 인증**: 쿠키 기반에서 세션 기반으로 변경

### 3. 코드 품질 향상
- **중복 코드 제거**: 공통 레이아웃 템플릿화
- **에러 핸들링**: 일관된 에러 처리
- **환경 설정 분리**: 설정 파일 외부화
- **로깅 시스템**: 디버깅 및 모니터링 개선

### 4. 사용자 경험 개선
- **반응형 디자인**: 모바일 친화적 UI
- **모던 CSS**: Flexbox 및 Grid 사용
- **JavaScript 모듈화**: 기능별 분리 및 재사용성 향상

## 프로젝트 구조

```
blog/
├── config/                 # 설정 파일들
│   ├── database.php       # 데이터베이스 설정
│   └── config.php         # 애플리케이션 설정
├── src/                   # 소스 코드
│   ├── Controllers/       # 컨트롤러
│   ├── Models/           # 모델
│   ├── Core/             # 핵심 클래스들
│   └── Database/         # 데이터베이스 관련
├── views/                # 뷰 템플릿
│   ├── layouts/          # 레이아웃 템플릿
│   ├── home/             # 홈페이지 뷰
│   ├── auth/             # 인증 관련 뷰
│   └── posts/            # 게시글 관련 뷰
├── public/               # 공개 디렉토리
│   ├── index.php         # 진입점
│   ├── css/              # 스타일시트
│   ├── js/               # JavaScript
│   └── res/              # 리소스 파일들
├── composer.json         # Composer 설정
└── README.md            # 프로젝트 문서
```

## 설치 및 실행

### 1. 의존성 설치
```bash
composer install
```

### 2. 데이터베이스 설정
`config/database.php` 파일에서 데이터베이스 연결 정보를 수정하세요.

### 3. 웹 서버 설정
Apache의 DocumentRoot를 `public/` 디렉토리로 설정하거나, 
Nginx를 사용하는 경우 `public/` 디렉토리를 서빙하도록 설정하세요.

### 4. 권한 설정
```bash
chmod -R 755 public/
chmod -R 644 config/
```

## 주요 기능

### 인증 시스템
- 로그인/로그아웃
- 세션 기반 인증
- 권한 관리 (글쓰기 제한)

### 게시글 관리
- 게시글 작성/수정/삭제
- 카테고리별 분류
- 검색 기능
- 페이지네이션

### 사용자 인터페이스
- 반응형 디자인
- 모던한 UI/UX
- 접근성 개선

## 기술 스택

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Editor**: SmartEditor2
- **Package Manager**: Composer

## 보안 고려사항

1. **SQL 인젝션 방지**: PDO Prepared Statements 사용
2. **XSS 방지**: 입력값 검증 및 출력 이스케이프
3. **CSRF 방지**: 토큰 기반 검증
4. **세션 보안**: 세션 하이재킹 방지
5. **파일 업로드**: 파일 타입 및 크기 제한

## 성능 최적화

1. **데이터베이스**: 인덱스 최적화
2. **캐싱**: 세션 및 쿼리 캐싱
3. **압축**: CSS/JS 압축
4. **CDN**: 정적 리소스 CDN 사용 권장

## 개발 가이드

### 새로운 컨트롤러 추가
```php
<?php
namespace Blog\Controllers;

use Blog\Controllers\BaseController;

class NewController extends BaseController
{
    public function index()
    {
        // 컨트롤러 로직
        $this->render('view-name', ['data' => $data]);
    }
}
```

### 새로운 모델 추가
```php
<?php
namespace Blog\Models;

use Blog\Database\Database;

class NewModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getData()
    {
        return $this->db->fetchAll("SELECT * FROM table");
    }
}
```

## 라이센스

이 프로젝트는 MIT 라이센스 하에 배포됩니다.

## 기여하기

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 문의

- 이메일: bae4969@naver.com
- GitHub: https://github.com/bae4969
