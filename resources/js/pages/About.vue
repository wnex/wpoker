<template>
	<vue-markdown
		:html="true"
		:anchorAttributes="anchorAttributes"
		:source="`### wPoker: Онлайн-приложение для скрам-оценки задач

wPoker — это онлайн-приложение, разработанное специально для проведения скрам-оценок задач и улучшения совместной работы команд. Приложение доступно на различных платформах: мобильные устройства (телефоны) и персональные компьютеры.

#### Основные функции:

1. **Создание и управление комнатами**
   - Создайте отдельную комнату для своей команды.
   - Установите пароль, чтобы контролировать вход в вашу комнату.
   - Передайте права на управление комнатой другому пользователю.

2. **Оценка задач**
   - Добавляйте и редактируйте список задач.
   - Используйте стандартные наборы карточек или создавайте свои собственные.

3. **Скрам-оценка**
   - Ведите онлайн-оценку задач с вашей командой в реальном времени.
   - Используйте внутренний чат для обсуждения и комментариев по ходу работы.

4. **Пользовательский контроль**
   - Добавляйте пользователей в качестве наблюдателей, которые могут наблюдать за процессом оценки, но не участвовать в голосовании.
   - Выгоняйте неактивных или бесполезных участников из комнаты.

5. **Сохранение данных**
   - Информация о комнатах, задачах и подключениях пользователей сохраняется на нашем надежном сервере.
   - Личные данные пользователя (например, имя) хранятся на сервере только в течение сессии.

#### Особенности:

- **Мультиплатформенность:** Приложение доступно для мобильных устройств и компьютеров.
- **Гибкость:** Используйте стандартные наборы карточек или создавайте свои собственные под ваши нужды.
- **Альтернативная оценка:** Если ваши задачи ведутся где-то еще, вы можете проводить оценку без списка задач.

#### Заключение

Благодаря wPoker ваша команда будет работать более эффективно и продуктивно. Начните прямо сейчас!

---

### wPoker: Online Scrum Task Estimation Application

wPoker is online application designed for conducting scrum task estimation sessions and enhancing team collaboration. The app is available on various platforms, including mobile devices (phones) and personal computers.

#### Key Features:

1. **Room Creation and Management**
   - Create a dedicated room for your team.
   - Set a password to control access to your room.
   - Transfer ownership of the room to another user.

2. **Task Estimation**
   - Add and edit task lists.
   - Use standard card sets or create your own custom sets.

3. **Scrum Estimations**
   - Conduct online task estimation sessions with your team in real-time.
   - Utilize an internal chat for discussions and comments during the session.

4. **User Control**
   - Add users as observers who can watch the estimation process but cannot vote.
   - Remove inactive or unproductive participants from the room.

5. **Data Storage**
   - Information about rooms, tasks, and user connections is stored on our secure server.
   - Personal user data (such as name) is stored only during the session.

#### Features:

- **Multi-platform Support:** The application is available for both mobile devices and computers.
- **Flexibility:** Use standard card sets or create your own custom sets tailored to your needs.
- **Alternative Estimation:** Conduct estimations without a task list if your tasks are managed elsewhere.

#### Conclusion

With wPoker, your team will work more efficiently and productively. Start now!`"
	></vue-markdown>
</template>

<script>
	import VueMarkdown from 'vue-markdown';

	import Prism from 'prismjs';
	import 'prismjs/themes/prism.css';

	import 'prismjs/components/prism-markup-templating.js';
	import 'prismjs/components/prism-php';
	import 'prismjs/components/prism-bash';

	export default {
		props: ['socket'],

		mounted() {
			this.$root.setTitle();
		},

		components: {
			VueMarkdown,
		},

		computed: {
			anchorAttributes() {
				return {
					target: 'blank',
					rel: 'nofollow',
				}
			},
		},
	}
</script>

<style scope>
	blockquote {
		background: #f9f9f9;
		border-left: 10px solid #ccc;
		margin: 1.5em 10px;
		padding: 1em 10px 0.1em 10px;
		quotes: "\201C""\201D""\2018""\2019";
	}
</style>
