<?php

namespace TM\UserBundle\Command;

use Assert\Assertion as Assert;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use TM\AppBundle\Model\User;
use TM\ResourceBundle\Model\ValueObject\Locale;
use TM\ResourceBundle\Uuid\Uuid;
use TM\ResourceBundle\Uuid\UuidInterface;
use TM\UserBundle\Exception\UserException;

class CreateUserCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('tm:user:create')
            ->setDescription('Create a user')
            ->setDefinition([
                new InputArgument('id', InputArgument::REQUIRED, 'The id'),
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                new InputArgument('email', InputArgument::REQUIRED, 'The email'),
                new InputArgument('password', InputArgument::REQUIRED, 'The password'),
                new InputArgument('locale', InputArgument::REQUIRED, 'The Locale'),
                new InputOption('inactive', null, InputOption::VALUE_NONE, 'Set the user as inactive'),
            ])
            ->setHelp(<<<EOT
The <info>tm:user:create</info> command creates a user:

  <info>php app/console tm:user:create 05979d32-f614-46a1-8643-2bf402a8bab4 matthieu</info>

This interactive shell will ask you for an email and then a password.

You can alternatively specify the email and password as the second and third arguments:

  <info>php app/console tm:user:create 05979d32-f614-46a1-8643-2bf402a8bab4 matthieu matthieu@example.com mypassword</info>

You can create an inactive user (will not be able to log in):

  <info>php app/console tm:user:create 05979d32-f614-46a1-8643-2bf402a8bab4 thibault --inactive</info>

EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id         = Uuid::fromString($input->getArgument('id'));
        $username   = $input->getArgument('username');
        $email      = $input->getArgument('email');
        $password   = $input->getArgument('password');
        $inactive   = $input->getOption('inactive');
        $locale     = $input->getArgument('locale');

        $this->createUser($id, $username, $password, $email, !$inactive, $locale);

        $output->writeln(sprintf('Created user <comment>%s</comment>', $username));
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('id')) {
            $question = new Question('Please choose an id <press enter to auto-generate>: ');
            $question->setValidator(function($id) {
                if (empty($id)) {
                    $id = Uuid::create()->toString();
                }

                Assert::uuid($id, 'ID must be a valid version 4 UUID');

                return $id;
            });
            $id = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument('id', $id);
        }

        if (!$input->getArgument('username')) {
            $question = new Question('Please choose a username:');
            $question->setValidator(function($username) {
                if (empty($username)) {
                    throw new \Exception('Username can not be empty');
                }

                return $username;
            });
            $username = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument('username', $username);
        }

        if (!$input->getArgument('email')) {
            $question = new Question('Please choose an email:');
            $question->setValidator(function($email) {
                if (empty($email)) {
                    throw new \Exception('Email can not be empty');
                }

                return $email;
            });
            $email = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument('email', $email);
        }

        if (!$input->getArgument('password')) {
            $question = new Question('Please choose a password:');
            $question->setValidator(function($password) {
                if (empty($password)) {
                    throw new \Exception('Password can not be empty');
                }

                return $password;
            });
            $question->setHidden(true);
            $password = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument('password', $password);
        }

        if (!$input->getArgument('locale')) {
            $question = new ChoiceQuestion(
                'Please choose a locale <en_GB>:',
                Locale::getAvailableLocales(),
                'en_GB'
            );
            $question->setErrorMessage('Locale "%s" is not valid');

            $locale = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument('locale', $locale);
        }
    }

    /**
     * @param UuidInterface $id
     * @param string $username
     * @param string $password
     * @param string $email
     * @param bool $active
     * @param string $locale
     * @return User
     * @throws UserException
     */
    private function createUser(
        UuidInterface $id,
        string $username,
        string $password,
        string $email,
        bool $active,
        string $locale
    ) : User {
        $repository = $this->getContainer()->get('tm.registry.repository')->getUserRepository();

        if (null !== $user = $repository->findOneBy(['usernameCanonical' => $username])) {
            throw UserException::usernameNotUnique($username);
        }

        $user = $repository->createUser(
            $id,
            $username,
            $password,
            $email,
            $active
        );

        $user->setLocale(new Locale($locale));

        $repository->save($user);

        return $user;
    }
}
